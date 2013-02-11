<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;
use SmartCore\Bundle\EngineBundle\Controller;
use JMS\AopBundle\JMSAopBundle;

class HelloController extends BaseController
{    
    public function indexAction()
    {

//        $data = $this->get('http_kernel')->

        return new Response("<!DOCTYPE html>\n<html>\n<body>\nHello World!\n</body>\n</html>");
    }
}
