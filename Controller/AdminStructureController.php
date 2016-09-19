<?php

namespace SmartCore\Bundle\CMSBundle\Controller;

use Smart\CoreBundle\Controller\Controller;
use SmartCore\Bundle\CMSBundle\Entity\Folder;
use SmartCore\Bundle\CMSBundle\Entity\Node;
use SmartCore\Bundle\CMSBundle\Entity\Region;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AdminStructureController extends Controller
{
    /**
     * @return Response
     */
    public function structureAction()
    {
        if (null === $this->get('cms.folder')->get(1)) {
            return $this->redirect($this->generateUrl('cms_admin_structure_folder_create'));
        }

        return $this->render('CMSBundle:AdminStructure:structure.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @todo пагинация и табы.
     */
    public function trashAction()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        return $this->render('CMSBundle:AdminStructure:trash.html.twig', [
            'deleted_folders' => $em->getRepository('CMSBundle:Folder')->findDeleted(),
            'deleted_nodes'   => $em->getRepository('CMSBundle:Node')->findDeleted(),
        ]);
    }

    /**
     * @param Folder $folder
     *
     * @return RedirectResponse
     */
    public function trashRestoreFolderAction(Folder $folder)
    {
        $folder->setIsDeleted(false);

        $this->persist($folder, true);

        $this->addFlash('success', 'Папка восстановлена.');

        return $this->redirectToRoute('cms_admin_structure_trash');
    }

    /**
     * @param Folder $folder
     *
     * @return RedirectResponse
     */
    public function trashPurgeFolderAction(Folder $folder)
    {
        $this->get('cms.folder')->remove($folder);

        $this->addFlash('success', 'Папка удалена.');

        return $this->redirectToRoute('cms_admin_structure_trash');
    }

    /**
     * @param Node $node
     *
     * @return RedirectResponse
     */
    public function trashRestoreNodeAction(Node $node)
    {
        $node->setIsDeleted(false);

        // Если у модуля есть роутинги, тогда нода подключается к папке как роутер.
        $folder = $node->getFolder();
        if ($this->container->has('cms.router_module.'.$node->getModule()) and !$folder->getRouterNodeId()) {
            $folder->setRouterNodeId($node->getId());
        }

        $this->persist($node, true);

        $this->addFlash('success', 'Нода восстановлена.');

        return $this->redirectToRoute('cms_admin_structure_trash');
    }

    /**
     * @param Node $node
     *
     * @return RedirectResponse
     */
    public function trashPurgeNodeAction(Node $node)
    {
        $this->get('cms.node')->remove($node);

        $this->addFlash('success', 'Нода удалена.');

        return $this->redirectToRoute('cms_admin_structure_trash');
    }

    /**
     * Отображение списка всех регионов, а также форма добавления нового.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function regionIndexAction(Request $request)
    {
        $engineRegion = $this->get('cms.region');

        $region = $engineRegion->create();
        $region->setUser($this->getUser());

        $form = $engineRegion->createForm($region);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $engineRegion->update($form->getData());
                $this->addFlash('success', 'Область создана.');

                return $this->redirectToRoute('cms_admin_structure_region');
            }
        }

        return $this->render('CMSBundle:AdminStructure:region_index.html.twig', [
            'all_regions' => $engineRegion->all(),
            'form'        => $form->createView(),
        ]);
    }

    /**
     * Редактирование области.
     *
     * @param Request $request
     * @param Region  $region
     *
     * @return Response|RedirectResponse
     */
    public function regionEditAction(Request $request, Region $region)
    {
        $form = $this->get('cms.region')->createForm($region);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($request->request->has('update')) {
                if ($form->isValid()) {
                    $this->get('cms.region')->update($form->getData());
                    $this->addFlash('success', 'Область обновлена.');

                    return $this->redirect($this->generateUrl('cms_admin_structure_region'));
                }
            } elseif ($request->request->has('delete')) {
                $region = $form->getData();

                if ('content' == $region->getName()) {
                    $this->addFlash('error', 'Нельзя удалить область content');
                } elseif (0 < $this->get('cms.node')->countInRegion($region)) {
                    $this->addFlash('error', 'Нельзя удалить область пока в неё включены модули');
                } else {
                    $this->get('cms.region')->remove($region);
                    $this->addFlash('success', 'Область удалена.');

                    return $this->redirect($this->generateUrl('cms_admin_structure_region'));
                }
            }
        }

        return $this->render('CMSBundle:AdminStructure:region_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Создание папки.
     *
     * @param Request      $request
     * @param Folder|null  $parent
     *
     * @return Response|RedirectResponse
     */
    public function folderCreateAction(Request $request, Folder $parent = null)
    {
        $engineFolder = $this->get('cms.folder');

        $folder = $engineFolder->create();
        $folder->setUser($this->getUser());

        if (empty($parent)) {
            $folder->setTitle($this->get('translator')->trans('Homepage'));
        } else {
            $folder->setParentFolder($parent);
        }

        $form = $engineFolder->createForm($folder);

        // Для корневой папки удаляются некоторые поля формы
        if (empty($parent)) {
            $form
                ->remove('uri_part')
                ->remove('parent_folder')
                ->remove('router_node_id')
                ->remove('is_active')
                ->remove('is_file')
                ->remove('pos');
        }

        if ($request->isMethod('POST')) {
            if ($request->request->has('create')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $engineFolder->update($form->getData());

                    $this->get('tagcache')->deleteTag('folder');
                    $this->addFlash('success', 'Папка создана.');

                    if ($request->query->has('redirect_to')) {
                        return $this->get('cms.router')->redirect($folder);
                    }

                    return $this->redirect($this->generateUrl('cms_admin_structure'));
                }
            } elseif ($request->request->has('delete')) {
                die('@todo');
            }
        }

        return $this->renderView('CMSBundle:AdminStructure:folder_create.html.twig', [
            'form'       => $form->createView(),
            'folderPath' => $this->get('cms.folder')->getUri($parent),
        ]);
    }

    /**
     * Редактирование папки.
     *
     * @param Request     $request
     * @param Folder|null $folder
     *
     * @return Response|RedirectResponse
     */
    public function folderEditAction(Request $request, Folder $folder = null)
    {
        if (empty($folder)) {
            return $this->redirect($this->generateUrl('cms_admin_structure'));
        }

        $form = $this->get('cms.folder')->createForm($folder);

        // Для корневой папки удаляются некоторые поля формы
        if (1 == $folder->getId()) {
            $form
                ->remove('uri_part')
                ->remove('parent_folder')
                ->remove('is_active')
                ->remove('is_file')
                ->remove('pos');
        }

        if ($request->isMethod('POST')) {
            if ($request->request->has('update')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $this->get('cms.folder')->update($form->getData());

                    $this->get('tagcache')->deleteTag('folder');
                    $this->addFlash('success', 'Папка обновлена.');

                    if ($request->query->has('redirect_to')) {
                        return $this->get('cms.router')->redirect($folder);
                    }

                    return $this->redirect($this->generateUrl('cms_admin_structure'));
                }
            } elseif ($request->request->has('delete')) {
                $form->handleRequest($request);

                /** @var $folder \SmartCore\Bundle\CMSBundle\Entity\Folder */
                $folder = $form->getData();
                $folder->setIsDeleted(true);

                $this->persist($folder, true);

                $this->get('tagcache')->deleteTag('node');
                $this->get('tagcache')->deleteTag('folder');

                $this->addFlash('success', 'Папка <b>'.$folder->getTitle().'</b> ('.$folder->getId().') удалена.');

                if ($request->query->has('redirect_to')) {
                    return $this->get('cms.router')->redirect($folder->getParentFolder());
                }

                return $this->redirect($this->generateUrl('cms_admin_structure'));
            }
        }

        $allow_delete = $folder->getId() != 1 ? true : false;

        if ($allow_delete and ($folder->getChildren()->count() > 0 or $folder->getNodes()->count() > 0 or $folder->getRegions()->count() > 0)) {
            $allow_delete = false;
        }

        return $this->renderView('CMSBundle:AdminStructure:folder_edit.html.twig', [
            'allow_delete'  => $allow_delete,
            'folderPath'    => $this->get('cms.folder')->getUri($folder),
            'form'          => $form->createView(),
        ]);
    }

    /**
     * @param Request     $request
     * @param int         $id
     * @param string|null $slug
     *
     * @return Response
     */
    public function nodeAction(Request $request, $id, $slug = null)
    {
        $node = $this->get('cms.node')->get($id);

        $controller = $this->get('cms.router')->matchModuleAdmin($node->getModule(), '/'.$slug);
        $controller['_node'] = $node;

        $subRequest = $this->get('request_stack')->getCurrentRequest()->duplicate($request->query->all(), null, $controller);

        $response = $this->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        if ($response->isRedirection() and $request->query->has('redirect_to')) {
            return $this->redirect($request->query->get('redirect_to'));
        }

        return $response;
    }

    /**
     * Создание новой ноды.
     *
     * @param Request $request
     * @param int $folder_pid
     *
     * @return RedirectResponse|Response
     */
    public function nodeCreateAction(Request $request, $folder_pid = 1)
    {
        if (null === $folder = $this->get('cms.folder')->get($folder_pid)) {
            return $this->redirect($this->generateUrl('cms_admin_structure_folder_create'));
        }

        $cmsNode = $this->get('cms.node');
        $node = $cmsNode->create();
        $node->setUser($this->getUser())
            ->setFolder($folder);

        $form = $cmsNode->createForm($node);

        if ($request->isMethod('POST')) {
            if ($request->request->has('create')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    /** @var $createdNode \SmartCore\Bundle\CMSBundle\Entity\Node */
                    $createdNode = $form->getData();

                    $cmsNode->update($createdNode);

                    // Если у модуля есть роутинги, тогда нода подключается к папке как роутер.
                    $folder = $createdNode->getFolder();
                    if ($this->container->has('cms.router_module.'.$createdNode->getModule()) and !$folder->getRouterNodeId()) {
                        $folder->setRouterNodeId($createdNode->getId());
                        $this->get('cms.folder')->update($folder);
                    }

                    $this->get('tagcache')->deleteTag('node');
                    $this->addFlash('success', 'Нода создана.');

                    if ('front' === $request->query->get('redirect_to')) {
                        return $this->get('cms.router')->redirect($createdNode);
                    }

                    return $this->redirectToRoute('cms_admin_structure_node_properties', ['id' => $createdNode->getId()]);
                }
            } elseif ($request->request->has('delete')) {
                die('@todo');
            }
        }

        return $this->render('CMSBundle:AdminStructure:node_create.html.twig', [
            'form'       => $form->createView(),
            'folderPath' => $this->get('cms.folder')->getUri($folder_pid),
        ]);
    }

    /**
     * Редактирование ноды.
     *
     * @param Request $request
     * @param int $id
     *
     * @return RedirectResponse|Response
     */
    public function nodeEditAction(Request $request, $id)
    {
        $cmsNode = $this->get('cms.node');
        $node = $cmsNode->get($id);

        if (empty($node)) {
            return $this->redirect($this->generateUrl('cms_admin_structure'));
        }

        $nodeParams = $node->getParams();

        $form = $cmsNode->createForm($node);
        $propertiesFormType = $cmsNode->getPropertiesFormType($node->getModule());
        $form_properties = $this->createForm($propertiesFormType, $nodeParams);

        $form->remove('module');

        if ($request->isMethod('POST')) {
            if ($request->request->has('update')) {
                $form->handleRequest($request);
                $form_properties->handleRequest($request);

                if ($form->isValid()
                    and (// @todo отрефакторить!!!
                        (empty($nodeParams) and !$form_properties->isValid())
                        or (!empty($nodeParams) and $form_properties->isValid())
                        or (empty($nodeParams) and $form_properties->isValid())
                    )
                ) {
                    /** @var $updatedNode \SmartCore\Bundle\CMSBundle\Entity\Node */
                    $updatedNode = $form->getData();
                    $updatedNode->setParams($form_properties->getData());
                    $cmsNode->update($updatedNode);

                    $this->get('tagcache')->deleteTag('node');
                    $this->addFlash('success', 'Параметры модуля <b>'.$node->getModule().'</b> ('.$node->getId().') обновлены.');

                    if ($request->query->has('redirect_to')) {
                        return $this->get('cms.router')->redirect($updatedNode);
                    }

                    return $this->redirect($this->generateUrl('cms_admin_structure'));
                } else {
                    ld('Ошибка валидации формы');
                    ld($nodeParams);
                    ld($form_properties->isValid());
                }
            } elseif ($request->request->has('delete')) {
                $form->handleRequest($request);

                /** @var $node \SmartCore\Bundle\CMSBundle\Entity\Node */
                $node = $form->getData();
                $node
                    ->setIsDeleted(true)
                    ->setDeletedAt(new \DateTime())
                ;

                $node->getFolder()->setRouterNodeId(null);

                $em = $this->getDoctrine()->getManager();
                $em->persist($node);
                $em->flush();

                $this->get('tagcache')->deleteTag('node');

                $this->addFlash('success', 'Нода <b>'.$node->getModule().'</b> ('.$node->getId().') удалена.');

                if ($request->query->has('redirect_to')) {
                    return $this->get('cms.router')->redirect($node);
                }

                return $this->redirect($this->generateUrl('cms_admin_structure'));
            }
        }

        return $this->render('CMSBundle:AdminStructure:node_edit.html.twig', [
            'allow_delete'    => true,
            'form'            => $form->createView(),
            'form_properties' => $form_properties->createView(),
            'form_properties_template' => $propertiesFormType->getTemplate(),
            'node'            => $node,
        ]);
    }
}
