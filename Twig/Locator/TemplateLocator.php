<?php

namespace SmartCore\Bundle\CMSBundle\Twig\Locator;

use Liip\ThemeBundle\Locator\TemplateLocator as BaseTemplateLocator;
use Symfony\Component\Templating\TemplateReferenceInterface;

class TemplateLocator extends BaseTemplateLocator
{
    public function clearCacheForModule($prefix)
    {
        if (empty($this->cache)) {
            return;
        }

        $prefix .= 'ModuleBundle';

        foreach ($this->cache as $tpl => $__dummy_path) {
            if (0 === strpos($tpl, $prefix.':')) {
                unset($this->cache[$tpl]);
            }
        }
    }

    /**
     * Подмешивание темы модуля в ключ кеша.
     *
     * Returns a full path for a given file.
     *
     * @param TemplateReferenceInterface $template A template
     *
     * @return string The full path for the file
     */
    protected function getCacheKey($template)
    {
        $name = $template->getLogicalName();

        if ($this->activeTheme) {
            $name .= '|module_theme='.$this->getLocator()->getModuleTheme().'|active_theme'.$this->activeTheme->getName();
        }

        return $name;
    }

    /**
     * @return ModuleThemeLocator
     */
    public function getLocator()
    {
        return $this->locator;
    }
}
