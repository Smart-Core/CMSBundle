<?php

namespace SmartCore\Bundle\EngineBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ModuleControllerModifier
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onView(GetResponseForControllerResultEvent $event)
    {
        $response = new Response();
        $response->setContent($event->getControllerResult());

        $event->setResponse($response);
    }
    
    public function onController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        if ($event->getRequest()->attributes->has('_node')) {
            $node = $event->getRequest()->attributes->get('_node');
            
            $controller[0]->setNode($node);

            $event->getRequest()->attributes->remove('_node');
        }
    }

    public function onRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::SUB_REQUEST === $event->getRequestType()) {
            $controller = explode(':', $event->getRequest()->attributes->get('_controller'));
            
            if (is_numeric($controller[0])) {
                $node = $this->container->get('engine.node')->getProperties($controller[0]);

                if (empty($controller[1])) {
                    $controller[1] = $node['controller'];
                }

                if (empty($controller[2])) {
                    $controller[2] = $node['action'];
                }

                $event->getRequest()->attributes->set('_controller', $node['module_id'] . 'Module:' . $controller[1] . ':' . $controller[2]);
                $event->getRequest()->attributes->set('_node', $node);
                if (!empty($node['arguments']) and is_array($node['arguments'])) {
                    foreach ($node['arguments'] as $name => $value) {
                        $event->getRequest()->attributes->set($name, $value);
                    }
                }
            }
        }
    }
}