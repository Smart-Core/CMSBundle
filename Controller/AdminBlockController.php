<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminBlockController extends Controller
{
    /**
     * Отображение списка всех блоков, а также форма добавления нового.
     *
     * @param Request $request
     */
    public function indexAction(Request $request)
    {
        $block = $this->get('engine.block')->create();
        $block->setCreateByUserId($this->getUser()->getId());

        $form = $this->get('engine.block')->createForm($block);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $this->get('engine.block')->update($form->getData());
                $this->get('session')->getFlashBag()->add('notice', 'Блок создан.');
                return $this->redirect($this->generateUrl('cmf_admin_structure_block'));
            }
        }

        return $this->render('SmartCoreEngineBundle:Admin:block.html.twig', [
            'all_blocks'  => $this->get('engine.block')->all(),
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
        $block = $this->get('engine.block')->get($id);

        if (empty($block)) {
            return $this->redirect($this->generateUrl('cmf_admin_structure_block'));
        }

        $form = $this->get('engine.block')->createForm($block);

        if ($request->isMethod('POST')) {
            if ($request->request->has('update')) {
                $form->bind($request);
                if ($form->isValid()) {
                    $this->get('engine.block')->update($form->getData());
                    $this->get('session')->getFlashBag()->add('notice', 'Блок обновлён.');
                    return $this->redirect($this->generateUrl('cmf_admin_structure_block'));
                }
            } else if ($request->request->has('delete')) {
                $this->get('engine.block')->remove($form->getData());
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
