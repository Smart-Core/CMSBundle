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

class AdminNodeController extends Controller
{
    public function nodeAction(Request $request, $id, $slug = null)
    {
        return $this->forward("$id:Admin:index", ['slug' => $slug]);
    }

    public function editAction(Request $request, $id)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        /** @var $node Node */
        $node = $em->find('SmartCoreEngineBundle:Node', $id);

        if (empty($node)) {
            return $this->redirect($this->generateUrl('cmf_admin_structure'));
        }

        $form = $this->createForm(new NodeFormType(), $node);
        $form_properties = $this->createForm($this->get('engine.node_manager')->getPropertiesFormType($node->getModule()), $node->getParams());

        $form->remove('module');

        if ($request->isMethod('POST')) {
            if ($request->request->has('update')) {
                $form->bind($request);
                $form_properties->bind($request);
                if ($form->isValid() and $form_properties->isValid()) {
                    /** @var $updated_node Node */
                    $updated_node = $form->getData();
                    $updated_node->setParams($form_properties->getData());
                    $em->persist($updated_node);
                    $em->flush();

                    if ($request->isXmlHttpRequest()) {
                        // @todo проверять referer, и если нода по прежнему находится в наследованном пути, то редиректиться в реферер.
                        return new JsonResponse(['redirect' => $this->get('engine.folder')->getUri($updated_node->getFolder()->getId())]);
                    } else {
                        $this->get('session')->getFlashBag()->add('notice', 'Нода обновлена.');
                        return $this->redirect($this->generateUrl('cmf_admin_structure'));
                    }
                }
            } else if ($request->request->has('delete')) {
                die('@todo');
            }
        }

        return $this->renderView('SmartCoreEngineBundle:Admin:node_edit.html.twig', [
            'node_id' => $id,
            'html_head_title' => 'Edit node',
            'form' => $form->createView(),
            'form_action' => $this->generateUrl('cmf_admin_structure_node_properties', ['id' => $id]),
            'form_controls' => 'update',
            'form_properties' => $form_properties->createView(),
        ]);
    }

    public function createAction(Request $request, $folder_pid = 1)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        $node = new Node();
        $node->setCreateByUserId($this->getUser()->getId());
        $node->setFolder($em->find('SmartCoreEngineBundle:Folder', $folder_pid));

        $form = $this->createForm(new NodeFormType(), $node);

        if ($request->isMethod('POST')) {
            if ($request->request->has('create')) {
                $form->bind($request);
                if ($form->isValid()) {
                    $created_node = $form->getData();

                    // Свежесозданная нода выполняет свои действия, а также устанавливает параметры по умолчанию.
                    $this->get('engine.node_manager')->createNode($created_node);

                    $em->persist($created_node);
                    $em->flush();

                    if ($request->isXmlHttpRequest()) {
                        return new JsonResponse(['redirect' => $this->get('engine.folder')->getUri($created_node->getFolder()->getId())]);
                    } else {
                        $this->get('session')->getFlashBag()->add('notice', 'Нода создана.');
                        return $this->redirect($this->generateUrl('cmf_admin_structure_node_properties', ['id' => $created_node->getId()]));
                    }
                }
            } else if ($request->request->has('delete')) {
                die('@todo');
            }
        }

        return $this->renderView('SmartCoreEngineBundle:Admin:structure_edit.html.twig', [
            'html_head_title' => 'Create node',
            'form' => $form->createView(),
            'form_action' => $this->generateUrl('cmf_admin_structure_node_create'),
            'form_controls' => 'create',
        ]);
    }
}
