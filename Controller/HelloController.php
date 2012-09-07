<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

class HelloController extends BaseController
{    
    public function indexAction()
    {
        return new Response("<!DOCTYPE html>\n<html>\n<body>\nHello World!\n</body>\n</html>");
    }
}
