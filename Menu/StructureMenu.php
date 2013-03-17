<?php

namespace SmartCore\Bundle\EngineBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use SmartCore\Bundle\EngineBundle\Entity\Folder;

class StructureMenu extends ContainerAware
{
    /** @var $folderRepository \Doctrine\ORM\EntityRepository */
    protected $folderRepository;

    /** @var $em \Doctrine\ORM\EntityManager */
    protected $em;

    /**
     * Построение полной структуры, включая ноды.
     *
     * @param FactoryInterface  $factory
     * @param array             $options
     *
     * @return ItemInterface
     */
    public function full(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('full_structure');
        $menu->setChildrenAttributes(array(
            'class' => 'filetree',
            'id' => 'browser',
        ));

        $this->em = $this->container->get('doctrine')->getManager();
        $this->folderRepository = $this->container->get('doctrine')->getRepository('SmartCoreEngineBundle:Folder');

        $this->addChild($menu);

        return $menu;
    }

    /**
     * Рекурсивное построение дерева.
     *
     * @param ItemInterface $menu
     * @param Folder        $parent_folder
     */
    protected function addChild(ItemInterface $menu, Folder $parent_folder = null)
    {
        $folders = $this->folderRepository->findBy(
            array('parent_folder' => $parent_folder),
            array('pos' => 'ASC')
        );

        /** @var $folder Folder */
        foreach ($folders as $folder) {
            $uri = $this->container->get('router')->generate('cmf_admin_structure_folder', array('id' => $folder->getId()));
            $menu->addChild($folder->getTitle(), array('uri' => $uri))->setAttributes(array(
                'class' => 'folder',
                'title' => $folder->getDescr(),
                'id' => 'folder_id_' . $folder->getId(),
            ));

            $this->addChild($menu[$folder->getTitle()], $folder);
        }
    }
}
