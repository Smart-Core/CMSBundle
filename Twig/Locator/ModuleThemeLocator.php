<?php

namespace SmartCore\Bundle\CMSBundle\Twig\Locator;

use Liip\ThemeBundle\Locator\FileLocator;

class ModuleThemeLocator extends FileLocator
{
    protected $moduleTheme;

    /**
     * @param string $moduleTheme
     *
     * @return $this
     */
    public function setModuleTheme($moduleTheme)
    {
        $this->moduleTheme = $moduleTheme;

        return $this;
    }

    /**
     * @param array $parameters
     *
     * @return array
     */
    protected function getPathsForBundleResource($parameters)
    {
        $parameters['%site_dir%']     = $this->kernel->getBundle('SiteBundle')->getPath().'/Resources';
        $parameters['%cms_dir%']      = $this->kernel->getBundle('CMSBundle')->getPath().'/Resources';
        $parameters['%module_theme%'] = $this->moduleTheme;

        return parent::getPathsForBundleResource($parameters);
    }
}
