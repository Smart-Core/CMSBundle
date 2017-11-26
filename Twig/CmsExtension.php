<?php

namespace SmartCore\Bundle\CMSBundle\Twig;

use DeviceDetector\DeviceDetector;
use SmartCore\Bundle\CMSBundle\CMSAppKernel;
use SmartCore\Bundle\CMSBundle\Entity\Node;
use SmartCore\Bundle\CMSBundle\Entity\Region;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CmsExtension extends \Twig_Extension
{
    use ContainerAwareTrait;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('cms_current_folder',  [$this, 'getCurrentFolder']),
            new \Twig_SimpleFunction('cms_folder',  [$this, 'getFolder']),
            new \Twig_SimpleFunction('cms_folder_path',  [$this, 'generateFolderPath']),
            new \Twig_SimpleFunction('cms_nodes_count_in_region',  [$this, 'nodesCountInRegion']),
            new \Twig_SimpleFunction('cms_get_notifications',  [$this, 'getNotifications']),
            new \Twig_SimpleFunction('cms_version',  [$this, 'getCMSKernelVersion']),
            new \Twig_SimpleFunction('cms_context_set',  [$this, 'cmsContextSet']),
            new \Twig_SimpleFunction('cms_device',  [$this, 'getDevice']),
        ];
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function cmsContextSet($key, $value)
    {
        $this->container->get('cms.context')->set($key, $value);
    }
    
    /**
     * Получение текущей папки.
     *
     * @param string|null $field
     *
     * @return null|\SmartCore\Bundle\CMSBundle\Entity\Folder
     */
    public function getCurrentFolder($field = null)
    {
        $folder = $this->container->get('cms.folder')->get($this->container->get('cms.context')->getCurrentFolderId());

        if (!empty($field)) {
            $method = 'get'.ucfirst($field);

            if (method_exists($folder, $method)) {
                return $folder->$method();
            }
        }

        return $folder;
    }

    /**
     * Получение папки.
     *
     * @param int $folderId
     * @param string|null $field
     *
     * @return null|\SmartCore\Bundle\CMSBundle\Entity\Folder
     */
    public function getFolder($folderId, $field = null)
    {
        $folder = $this->container->get('cms.folder')->get($folderId);

        if (!empty($field)) {
            $method = 'get'.ucfirst($field);

            if (method_exists($folder, $method)) {
                return $folder->$method();
            }
        }

        return $folder;
    }

    /**
     * Получение полной ссылки на папку, указав её id. Если не указать ид папки, то вернётся текущий путь.
     *
     * @param mixed|null $data
     *
     * @return string
     */
    public function generateFolderPath($data = null)
    {
        return $this->container->get('cms.folder')->getUri($data);
    }

    /**
     * @param  Region|int $region
     *
     * @return int
     */
    public function nodesCountInRegion($region)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        return $em->getRepository(Node::class)->countInRegion($region);
    }

    /**
     * @return array
     */
    public function getNotifications()
    {
        $data = [];

        foreach ($this->container->get('cms.module')->all() as $module) {
            $notices = $module->getNotifications();

            if (!empty($notices)) {
                $data['notifications'][$module->getName()] = $notices;
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getCMSKernelVersion()
    {
        return CMSAppKernel::VERSION;
    }

    /**
     * @return DeviceDetector
     */
    public function getDevice()
    {
        $userAgent = $this->container->get('request_stack')->getMasterRequest()->headers->get('user-agent');

        $dd = new DeviceDetector($userAgent);
        $dd->setCache(new \Doctrine\Common\Cache\PhpFileCache(
            $this->container->getParameter('kernel.cache_dir').'/device_detector')
        );
        $dd->skipBotDetection();
        $dd->parse();

        return $dd;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'smart_core_cms_twig_extension';
    }
}
