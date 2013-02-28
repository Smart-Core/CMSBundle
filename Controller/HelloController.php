<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use SmartCore\Bundle\EngineBundle\Controller\Controller;

class HelloController extends Controller
{    
    public function indexAction()
    {

//        $data = $this->get('http_kernel')->

        return new Response("<!DOCTYPE html>\n<html>\n<body>\nHello World!\n</body>\n</html>");
    }
}
