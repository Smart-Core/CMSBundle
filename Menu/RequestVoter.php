<?php

namespace SmartCore\Bundle\EngineBundle\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RequestVoter implements VoterInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function matchItem(ItemInterface $item)
    {
        $request = $this->container->get('request');

        if ($item->getUri() === $request->getRequestUri()) {
            // URL's completely match
            return true;
        } else if(
            $item->getUri() !== $request->getBaseUrl().'/' and
            $item->getUri() === substr($request->getRequestUri(), 0, strlen($item->getUri())) and
            $request->attributes->get('__selected_inheritance', true)
        ) {
            // URL isn't just "/" and the first part of the URL match
            return true;
        }
        return null;
    }
}
