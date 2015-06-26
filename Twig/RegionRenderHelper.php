<?php

namespace SmartCore\Bundle\CMSBundle\Twig;

class RegionRenderHelper
{
    public function __toString()
    {
        return $this->render();
    }

    public function render()
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        foreach ($this as $_dummy_nodeId => $response) {
            echo $response->getContent();
        }

        return '';
    }
}
