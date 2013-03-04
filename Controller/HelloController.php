<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use SmartCore\Bundle\EngineBundle\Controller\Controller;

class HelloController extends Controller
{    
    public function indexAction()
    {
        $this->get('html')
            ->doctype('xhtml')
            ->lang('en')
            ->title('hi :)')
        ;

        //return new Response("<!DOCTYPE html>\n<html>\n<body>\nHello World!\n</body>\n</html>");
        return $this->render('HtmlBundle::test.html.twig',array('hello' => 'Hello World!'));
    }
}
