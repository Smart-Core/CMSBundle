<?php

namespace SmartCore\Bundle\EngineBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ModuleControllerModifier
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }
        
//        $Reflector = new \ReflectionClass(get_class($event));
//        sc_dump($Reflector->getMethods());
        
        if ($event->getRequest()->attributes->has('_node')) {
            $node = $event->getRequest()->attributes->get('_node');
            
            
//            sc_dump(get_class($controller[0]));

            $controller[0]->setNode($node);
            
            $event->getRequest()->attributes->remove('_node');
            
//            sc_dump($event->getRequest()->attributes);
//            sc_dump($node);
        }
        
                
//        if ($controller[0] instanceof \SmartCore\Module\Texter\Controller\TestController) {
//            $Reflector = new \ReflectionClass(get_class($controller[0]));
//            sc_dump($Reflector->getMethods());
//            sc_dump($controller[0]->getParams());
//            sc_dump(get_class($controller[0]));
//            sc_dump($controller);exit;
//        }
    }
    
    public function onRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::SUB_REQUEST === $event->getRequestType()) {
            
//            sc_dump($event->getRequest()->attributes);
            
            $controller = explode(':', $event->getRequest()->attributes->get('_controller'));
            
//            sc_dump($controller);
            
            if (is_numeric($controller[0])) {
                $node = $this->container->get('engine.node')->getProperties($controller[0]);
                
                if (empty($controller[1])) {
                    $controller[1] = $node['controller'];
                }
                
                if (empty($controller[2])) {
                    $controller[2] = $node['action'];
                }
                
//                sc_dump($node['module_id'] . 'Module:' . $controller[1] . ':' . $controller[2]);
                
                $event->getRequest()->attributes->set('_controller', $node['module_id'] . 'Module:' . $controller[1] . ':' . $controller[2]);
                $event->getRequest()->attributes->set('_node', $node);
                
//            sc_dump($event->getRequest()->attributes);
                
            }
        }
    }
}