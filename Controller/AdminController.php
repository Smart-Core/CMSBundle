<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{    
    public function nodeAction(Request $request, $id, $slug)
    {
        return $this->forward("$id:Admin:index", array('slug' => $slug));
        //return new Response($id);
    }

    public function runAction($slug)
    {
        ld($slug);
        return new Response('runAction');
    }
}
