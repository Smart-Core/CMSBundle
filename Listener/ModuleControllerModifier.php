<?php

namespace SmartCore\Bundle\EngineBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
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

        if ($event->getRequest()->attributes->has('_eip')) {
            $controller[0]->setEip($event->getRequest()->attributes->get('_eip'));

            $event->getRequest()->attributes->remove('_eip');
        }

        if ($event->getRequest()->attributes->has('_node')) {
            /** @var $node \SmartCore\Bundle\EngineBundle\Entity\Node */
            $node = $event->getRequest()->attributes->get('_node');
            $controller[0]->setNode($node);
            $this->container->get('engine.context')->setCurrentNodeId($node->getId());
            $event->getRequest()->attributes->remove('_node');
        }
    }

    public function onRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::SUB_REQUEST === $event->getRequestType()) {
            $controller = explode(':', $event->getRequest()->attributes->get('_controller'));
            
            if (is_numeric($controller[0])) {
                /** @var $node \SmartCore\Bundle\EngineBundle\Entity\Node */
                $node = $this->container->get('engine.node_manager')->get($controller[0]);

                if (empty($controller[1])) {
                    $controller[1] = $node->getController();
                }

                if (empty($controller[2])) {
                    $controller[2] = $node->getAction();
                }

                $event->getRequest()->attributes->set('_controller', $node->getModule() . 'Module:' . $controller[1] . ':' . $controller[2]);
                $event->getRequest()->attributes->set('_node', $node);
                foreach ($node->getArguments() as $name => $value) {
                    $event->getRequest()->attributes->set($name, $value);
                }
            }
        }
    }

    public function onResponse(FilterResponseEvent $event)
    {
        $this->container->get('engine.context')->setCurrentNodeId(null);
    }
}
