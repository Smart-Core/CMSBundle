<?php

namespace SmartCore\Bundle\CMSBundle\Twig\Loader;

use Liip\ThemeBundle\ActiveTheme;
use Liip\ThemeBundle\Twig\Loader\FilesystemLoader as BaseFilesystemLoader;
use SmartCore\Bundle\CMSBundle\Entity\Node;
use SmartCore\Bundle\CMSBundle\Twig\Locator\TemplateLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;

class FilesystemLoader extends BaseFilesystemLoader
{
    /**
     * Фикс для Symfony v3.2.1 где 3-им аргументом приходит root_dir.
     *
     * FilesystemLoader constructor.
     *
     * @param FileLocatorInterface        $locator
     * @param TemplateNameParserInterface $parser
     * @param null                        $path
     * @param null                        $activeTheme
     */
    public function __construct(FileLocatorInterface $locator, TemplateNameParserInterface $parser, $path = null, $activeTheme = null)
    {
        if ($path instanceof ActiveTheme) {
            $activeTheme = $path;
        }

        parent::__construct($locator, $parser, $activeTheme);
    }

    /**
     * @param Node|null $node
     */
    public function setModuleTheme(Node $node = null)
    {
        if ($node) {
            $this->getTemplateLocator()->getLocator()->setModuleTheme($node->getTemplate());
            $this->clearCacheForModule($node->getModule());
        } else {
            $this->getTemplateLocator()->getLocator()->setModuleTheme(null);
        }
    }

    /**
     * @param string $prefix
     */
    public function clearCacheForModule($prefix)
    {
        if (empty($this->cache)) {
            return;
        }

        $this->getTemplateLocator()->clearCacheForModule($prefix);

        $prefix .= 'Module';

        foreach ($this->cache as $tpl => $__dummy_path) {
            if (0 === strpos($tpl, $prefix.':')) {
                unset($this->cache[$tpl]);
            }
        }
    }

    /**
     * @return TemplateLocator
     */
    protected function getTemplateLocator()
    {
        return $this->locator;
    }

    /**
     * Добавление пути после app.
     *
     * @param string $path      A path where to look for templates
     * @param string $namespace A path name
     *
     * @throws \Twig_Error_Loader
     */
    public function addCmsAppPath($path, $namespace = self::MAIN_NAMESPACE)
    {
        // invalidate the cache
        $this->cache = $this->errorCache = array();

        if (!is_dir($path)) {
            throw new \Twig_Error_Loader(sprintf('The "%s" directory does not exist.', $path));
        }

        $path = rtrim($path, '/\\');

        if (!isset($this->paths[$namespace])) {
            $this->paths[$namespace][] = $path;
        } else {
            $existAppPathKey = false;

            foreach ($this->paths[$namespace] as $key => $path2) {
                if (strpos($path2, 'app/Resources/')) {
                    $existAppPathKey = $key;
                }
            }

            if ($existAppPathKey === false) {
                array_unshift($this->paths[$namespace], $path);
            } else {
                $newPaths = [];

                foreach ($this->paths[$namespace] as $key => $path2) {
                    $newPaths[] = $path2;

                    if ($key == $existAppPathKey) {
                        $newPaths[] = $path;
                    }
                }

                $this->paths[$namespace] = $newPaths;
            }
        }
    }
}
