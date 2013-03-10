<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use SmartCore\Bundle\EngineBundle\Entity\Block;
use SmartCore\Bundle\EngineBundle\Entity\Folder;
use SmartCore\Bundle\EngineBundle\Form\Type\FolderFormType;
use SmartCore\Bundle\EngineBundle\Form\Type\BlockFormType;

class AdminStructureController extends Controller
{
    public function indexAction(Request $request)
    {
        return $this->renderView('SmartCoreEngineBundle:Admin:structure.html.twig', array(
            'title' => 'Редактировать раздел',
        ));
    }

    public function folderCreateAction(Request $request)
    {
        $form = $this->createForm(new FolderFormType());

        return $this->renderView('SmartCoreEngineBundle:Admin:structure.html.twig', array(
            'title' => 'Добавить раздел',
            'form'  => $form->createView(),
        ));
    }

    public function folderAction(Request $request, $pid = 0)
    {
        return $this->renderView('SmartCoreEngineBundle:Admin:structure.html.twig', array(
            'title' => 'Редактировать раздел',
        ));
    }

    public function nodeAction(Request $request)
    {
        return $this->renderView('SmartCoreEngineBundle:Admin:structure.html.twig', array(

        ));
    }

    public function nodeCreateAction(Request $request)
    {
        return $this->renderView('SmartCoreEngineBundle:Admin:structure.html.twig', array(

        ));
    }

    /**
     * Отображение списка всех блоков, а также форма добавления нового.
     *
     * @param Request $request
     * @param int $id
     */
    public function blockAction(Request $request, $id = 0)
    {
        $em = $this->EM();
        $block = $em->find('SmartCoreEngineBundle:Block', $id);

        if (empty($block)) {
            $block = new Block();
            $block->setCreateByUserId($this->getUser()->getId());
        }

        $form = $this->createForm(new BlockFormType(), $block);

        if ($request->getMethod() == 'POST') {
            if ($request->request->has('create') or $request->request->has('update')) {
                $form->bind($request);
                if ($form->isValid()) {
                    $em->persist($form->getData());
                    $em->flush();

                    return new RedirectResponse($this->generateUrl('cmf_admin_structure_block'));
                }
            } else if ($request->request->has('delete')) {
                die('@todo');
            }
        }

        if ($id) {
            $arg = array(
                'block_id'  => $id,
                'form_edit' => $form->createView(),
            );
        } else {
            $arg = array(
                'all_blocks'  => $em->getRepository('SmartCoreEngineBundle:Block')->findBy(array(), array('pos' => 'asc')),
                'form_create' => $form->createView(),
            );
        }

        return $this->renderView('SmartCoreEngineBundle:Admin:block.html.twig', $arg);
    }

    /**
     * Отображение структуры в виде дерева.
     */
    public function showTreeAction()
    {
        return $this->renderView('SmartCoreEngineBundle:Admin:tree.html.twig', array(

        ));
    }

}
