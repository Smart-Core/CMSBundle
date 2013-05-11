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

class AdminStructureController extends Controller
{
    public function indexAction(Request $request)
    {
        return $this->renderView('SmartCoreEngineBundle:Admin:structure.html.twig', []);
    }

    /**
     * Отображение структуры в виде дерева.
     */
    public function showTreeAction($folder_id = null, $node_id = null)
    {
        return $this->renderView('SmartCoreEngineBundle:Admin:tree.html.twig', [
            'folder_id' => $folder_id,
            'node_id' => $node_id,
        ]);
    }
}
