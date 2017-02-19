<?php

namespace SmartCore\Bundle\CMSBundle\Twig\Loader;

use Liip\ThemeBundle\ActiveTheme;
use Liip\ThemeBundle\Twig\Loader\FilesystemLoader as BaseFilesystemLoader;
use SmartCore\Bundle\CMSBundle\Entity\Node;
use SmartCore\Bundle\CMSBundle\Twig\Locator\TemplateLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;

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

    /**
     * @todo сделать пулл реквест с методом getCacheKey
     *
     * Returns the path to the template file.
     *
     * The file locator is used to locate the template when the naming convention
     * is the symfony one (i.e. the name can be parsed).
     * Otherwise the template is located using the locator from the twig library.
     *
     * @param string|TemplateReferenceInterface $template The template
     * @param bool                              $throw    When true, a \Twig_Error_Loader exception will be thrown if a template could not be found
     *
     * @return string The path to the template file
     *
     * @throws \Twig_Error_Loader if the template could not be found
     */
    protected function findTemplate($template, $throw = true)
    {
        $logicalName = (string) $template;

        $logicalName .= '|module_theme='.$this->locator->getLocator()->getModuleTheme(); // Добавлена эта строка

        if ($this->activeTheme) {
            $logicalName .= '|'.$this->activeTheme->getName();
        }

        if (isset($this->cache[$logicalName])) {
            return $this->cache[$logicalName];
        }

        $file = null;
        $previous = null;

        try {
            $templateReference = $this->parser->parse($template);
            $file = $this->locator->locate($templateReference);
        } catch (\Exception $e) {
            $previous = $e;

            // for BC
            try {
                $file = parent::findTemplate((string) $template);
            } catch (\Twig_Error_Loader $e) {
                $previous = $e;
            }
        }

        if (false === $file || null === $file) {
            if ($throw) {
                throw new \Twig_Error_Loader(sprintf('Unable to find template "%s".', $logicalName), -1, null, $previous);
            }

            return false;
        }

        return $this->cache[$logicalName] = $file;
    }
}
