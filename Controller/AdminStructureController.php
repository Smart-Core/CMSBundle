<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        return $this->renderView('SmartCoreEngineBundle:Admin:structure.html.twig', array(

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

    public function blockAction()
    {
        return $this->renderView('SmartCoreEngineBundle:Admin:block.html.twig', array(

        ));
    }

    public function showTreeAction()
    {
        return $this->renderView('SmartCoreEngineBundle:Admin:tree.html.twig', array(

        ));
    }

}
