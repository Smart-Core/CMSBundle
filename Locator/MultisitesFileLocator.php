<?php

namespace SmartCore\Bundle\EngineBundle\Locator;

use Liip\ThemeBundle\Locator\FileLocator as BaseFileLocator;

class MultisitesFileLocator extends BaseFileLocator
{
    /**
     * Locate Resource Theme aware. Only working for app/Resources
     *
     * @param string $name
     * @param string $dir
     * @param bool $first
     * @return string|array
     */
    public function locateAppResource($name, $dir = null, $first = true)
    {
        if ($this->kernel->getContainer()->getParameter('smart_core_engine.dir_sites') !== '') {
            $dir = $this->kernel->getContainer()->getParameter('kernel.root_dir') . '/' .
                   $this->kernel->getContainer()->getParameter('smart_core_engine.dir_sites') .
                   $this->kernel->getContainer()->get('engine.site')->getId() . '/Resources';
        }

        return parent::locateAppResource($name, $dir, $first);
    }
}