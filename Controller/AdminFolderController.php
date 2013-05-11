<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use SmartCore\Bundle\EngineBundle\Entity\Block;
use SmartCore\Bundle\EngineBundle\Entity\Folder;
use SmartCore\Bundle\EngineBundle\Entity\Node;
use SmartCore\Bundle\EngineBundle\Form\Type\BlockFormType;
use SmartCore\Bundle\EngineBundle\Form\Type\FolderFormType;
use SmartCore\Bundle\EngineBundle\Form\Type\NodeFormType;

class AdminFolderController extends Controller
{
    /**
     * Редактирование папки.
     */
    public function editAction(Request $request, $id = 1)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');
        $folder = $em->find('SmartCoreEngineBundle:Folder', $id);

        if (empty($folder)) {
            return $this->redirect($this->generateUrl('cmf_admin_structure'));
        }

        $form = $this->createForm(new FolderFormType(), $folder);

        // Для корневой папки удаляются некоторые поля формы
        if (1 == $id) {
            $form
                ->remove('uri_part')
                ->remove('parent_folder')
                ->remove('is_active')
                ->remove('is_file')
                ->remove('pos');
        }

        if ($request->isMethod('POST')) {
            if ($request->request->has('update')) {
                $form->bind($request);
                if ($form->isValid()) {
                    $em->persist($form->getData());
                    $em->flush();

                    if ($request->isXmlHttpRequest()) {
                        return new JsonResponse(['redirect' => $this->get('engine.folder')->getUri($folder->getId())]);
                    } else {
                        $this->get('session')->getFlashBag()->add('notice', 'Папка обновлена.');
                        return $this->redirect($this->generateUrl('cmf_admin_structure'));
                    }
                } else if ($request->isXmlHttpRequest()) {
                    // ld($form->getErrors()); // @todo разобраться почему не возвращаются ошибки.
                    return new JsonResponse(['notice' => 'Validation error.'], 400);
                }
            } else if ($request->request->has('delete')) {
                die('@todo');
            }
        }

        return $this->renderView('SmartCoreEngineBundle:Admin:structure_edit.html.twig', [
            'folder_id' => $id,
            'html_head_title' => 'Edit folder',
            'form' => $form->createView(),
            'form_action' => $this->generateUrl('cmf_admin_structure_folder', ['id' => $id]),
            'form_controls' => 'update',
            'allow_delete' => $id != 1 ? true : false,
        ]);
    }

    /**
     * Создание папки.
     */
    public function createAction(Request $request, $folder_pid = 1)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        $folder = new Folder();
        $folder->setCreateByUserId($this->getUser()->getId());
        $folder->setParentFolder($em->find('SmartCoreEngineBundle:Folder', $folder_pid));

        $form = $this->createForm(new FolderFormType(), $folder);

        if ($request->isMethod('POST')) {
            if ($request->request->has('create')) {
                $form->bind($request);
                if ($form->isValid()) {
                    $em->persist($form->getData());
                    $em->flush();

                    if ($request->isXmlHttpRequest()) {
                        return new JsonResponse(['redirect' => $this->get('engine.folder')->getUri($folder->getId())]);
                    } else {
                        $this->get('session')->getFlashBag()->add('notice', 'Папка создана.');
                        return $this->redirect($this->generateUrl('cmf_admin_structure'));
                    }
                }
            } else if ($request->request->has('delete')) {
                die('@todo');
            }
        }

        return $this->renderView('SmartCoreEngineBundle:Admin:structure_edit.html.twig', [
            'html_head_title' => 'Create folder',
            'form' => $form->createView(),
            'form_action' => $this->generateUrl('cmf_admin_structure_folder_create'),
            'form_controls' => 'create',
        ]);
    }
}
