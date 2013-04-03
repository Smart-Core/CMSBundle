<?php

namespace SmartCore\Bundle\EngineBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use SmartCore\Bundle\EngineBundle\Entity\Folder;
use SmartCore\Bundle\EngineBundle\Entity\Node;

class StructureMenu extends ContainerAware
{
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
        if (null == $parent_folder) {
            $folders = $this->em->getRepository('SmartCoreEngineBundle:Folder')->findByParent(null);
        } else {
            $folders = $parent_folder->getChildren();
        }

        /** @var $folder Folder */
        foreach ($folders as $folder) {
            $uri = $this->container->get('router')->generate('cmf_admin_structure_folder', array('id' => $folder->getId()));
            $menu->addChild($folder->getTitle(), array('uri' => $uri))->setAttributes(array(
                'class' => 'folder',
                'title' => $folder->getDescr(),
                'id' => 'folder_id_' . $folder->getId(),
            ));

            /** @var $sub_menu ItemInterface */
            $sub_menu = $menu[$folder->getTitle()];

            $this->addChild($sub_menu, $folder);

            /** @var $node Node */
            foreach ($folder->getNodes() as $node) {
                $uri = $this->container->get('router')->generate('cmf_admin_structure_node_properties', array('id' => $node->getId()));
                $sub_menu->addChild($node->getDescr() . ' (' . $node->getModule() . ':' . $node->getId() . ')', array('uri' => $uri))->setAttributes(array(
                    'title' => $node->getDescr(),
                    'id' => 'node_id_' . $node->getId(),
                ));
            }
        }
    }
}
