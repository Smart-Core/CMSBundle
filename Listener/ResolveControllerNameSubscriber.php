<?php

namespace SmartCore\Bundle\CMSBundle\Listener;

use SmartCore\Bundle\CMSBundle\Engine\EngineNode;
use Symfony\Bundle\FrameworkBundle\EventListener\ResolveControllerNameSubscriber as BaseResolveControllerNameSubscriber;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResolveControllerNameSubscriber extends BaseResolveControllerNameSubscriber
{
    /** @var EngineNode */
    protected $engineNodeManager;

    public function __construct(ControllerNameParser $parser, EngineNode $engineNodeManager)
    {
        $this->engineNodeManager = $engineNodeManager;

        parent::__construct($parser);
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $controller = $event->getRequest()->attributes->get('_controller');

        $parts = explode(':', $controller);

        if (is_numeric($parts[0])) {
            $node = $this->engineNodeManager->get($parts[0]);

            $controllerName = isset($parts[1]) ? $parts[1] : null;
            $actionName = isset($parts[2]) ? $parts[2] : 'index';

            foreach ($node->getController($controllerName, $actionName) as $key => $value) {
                $event->getRequest()->attributes->set($key, $value);
            }

            $event->getRequest()->attributes->set('_node', $node);
        }

        parent::onKernelRequest($event);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 24],
        ];
    }
}
