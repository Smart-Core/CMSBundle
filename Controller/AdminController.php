<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    public function indexAction(Request $request, $slug = null)
    {
        return $this->render('SmartCoreEngineBundle:Admin:_blank.html.twig', []);
    }

    public function nodeAction(Request $request, $id, $slug = null)
    {
        return $this->forward("$id:Admin:index", ['slug' => $slug]);
    }

    public function notFoundAction($slug)
    {
        return $this->renderView('SmartCoreEngineBundle:Admin:_blank.html.twig', []);
    }
}
