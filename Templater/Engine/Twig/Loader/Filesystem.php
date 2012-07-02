<?php
/**
 * В отличие от стандартного загрузчика, позволяет добавлять несуществующие пути.
 */
namespace SmartCore\Bundle\EngineBundle\Templater\Engine\Twig\Loader;

class Filesystem extends \Twig_Loader_Filesystem
{
    /**
     * Adds a path where templates are stored.
     *
     * @param string $path A path where to look for templates
     */
    public function addPath($path)
    {
        // invalidate the cache
        $this->cache = array();

        if (!is_dir($path)) {
            //throw new Twig_Error_Loader(sprintf('The "%s" directory does not exist.', $path));
        }

        $this->paths[] = $path;
    }
}