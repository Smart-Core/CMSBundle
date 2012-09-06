<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class HelloController extends Controller
{
    public function indexAction()
    {
        return new Response('Hello World!');
    }
}