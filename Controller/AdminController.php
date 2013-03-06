<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    public function indexAction(Request $request, $slug = null)
    {
        $this->get('html')
            ->title('Smart Core CMF')
            ->titlePrepend('Управление / ')
        ;

        return $this->render('SmartCoreEngineBundle:Admin:index.html.twig', array(

        ));
    }

    public function nodeAction(Request $request, $id, $slug = null)
    {
        return $this->forward("$id:Admin:index", array('slug' => $slug));
        //return new Response($id);
    }

    public function runAction($slug)
    {
//        ld($slug);
        //return new Response('runAction');
        return $this->renderView('SmartCoreEngineBundle::test.html.twig', array(

        ));
    }
}
