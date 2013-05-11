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

class AdminBlockController extends Controller
{
    /**
     * Отображение списка всех блоков, а также форма добавления нового.
     *
     * @param Request $request
     */
    public function indexAction(Request $request)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        $block = new Block();
        $block->setCreateByUserId($this->getUser()->getId());

        $form = $this->createForm(new BlockFormType(), $block);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                $this->get('session')->getFlashBag()->add('notice', 'Блок создан.');
                return $this->redirect($this->generateUrl('cmf_admin_structure_block'));
            }
        }

        return $this->render('SmartCoreEngineBundle:Admin:block.html.twig', [
            'all_blocks'  => $em->getRepository('SmartCoreEngineBundle:Block')->findBy([], ['position' => 'ASC']),
            'form_create' => $form->createView(),
        ]);
    }

    /**
     * Редактирование блока.
     *
     * @param Request $request
     * @param int $id
     */
    public function editAction(Request $request, $id = 0)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        $block = $em->find('SmartCoreEngineBundle:Block', $id);

        if (empty($block)) {
            return $this->redirect($this->generateUrl('cmf_admin_structure_block'));
        }

        $form = $this->createForm(new BlockFormType(), $block);

        if ($request->isMethod('POST')) {
            if ($request->request->has('update')) {
                $form->bind($request);
                if ($form->isValid()) {
                    $em->persist($form->getData());
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('notice', 'Блок обновлён.');
                    return $this->redirect($this->generateUrl('cmf_admin_structure_block'));
                }
            } else if ($request->request->has('delete')) {
                $em->remove($form->getData());
                $em->flush();

                $this->get('session')->getFlashBag()->add('notice', 'Блок удалён.');
                return $this->redirect($this->generateUrl('cmf_admin_structure_block'));
            }
        }

        return $this->render('SmartCoreEngineBundle:Admin:block.html.twig', [
            'block_id'  => $id,
            'form_edit' => $form->createView(),
        ]);
    }
}
