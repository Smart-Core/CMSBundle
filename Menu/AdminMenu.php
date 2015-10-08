<?php

namespace SmartCore\Bundle\CMSBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use SmartCore\Bundle\CMSBundle\Entity\Folder;

class AdminMenu extends ContainerAware
{
    /**
     * @param FactoryInterface $factory
     * @param array $options
     *
     * @return ItemInterface
     */
    public function main(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('admin_main');

        $menu->setChildrenAttribute('class', isset($options['class']) ? $options['class'] : 'sidebar-menu'); // nav navbar-nav
        $menu->addChild('Dashboard',     ['route' => 'cms_admin_index'])->setExtras(['beforeCode' => '<i class="fa fa-dashboard"></i>']);


        foreach ($this->container->get('cms.module')->all() as $module) {
            $module->buildAdminMenu($menu);
        }

        $systemItems = $menu->addChild('System', ['route' => 'cms_admin_system'])
            ->setAttribute('class', 'treeview')
            ->setExtras([
                'afterCode'  => '<i class="fa fa-angle-left pull-right"></i>',
                'beforeCode' => '<i class="fa fa-gear"></i>',
            ])
        ;
        $systemItems->setChildrenAttribute('class', 'treeview-menu');

        $systemItems->addChild('Site structure', ['route' => 'cms_admin_structure'])->setExtras(['beforeCode' => '<i class="fa fa-folder-open"></i>']);
        $systemItems->addChild('Files',          ['route' => 'cms_admin_files'])->setExtras(['beforeCode' => '<i class="fa fa-download"></i>']);
        $systemItems->addChild('Users',          ['route' => 'cms_admin_user'])->setExtras(['beforeCode' => '<i class="fa fa-users"></i>']);
        $systemItems->addChild('Configuration',  ['route' => 'cms_admin_config'])->setExtras(['beforeCode' => '<i class="fa fa-gears"></i>']);
        $systemItems->addChild('Appearance',     ['route' => 'cms_admin_appearance'])->setExtras(['beforeCode' => '<i class="fa fa-image"></i>']);

        $menu->addChild('Reports',       ['route' => 'cms_admin_reports'])->setExtras(['beforeCode' => '<i class="fa fa-file-excel-o"></i>']);
        //$menu->addChild('Help',          ['route' => 'cms_admin_help'])->setExtras(['beforeCode' => '<i class="fa fa-question"></i>']);

        return $menu;
    }

    public function user(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('admin_user');

        $menu->setExtra('select_intehitance', false);
        $menu->setChildrenAttribute('class', isset($options['class']) ? $options['class'] : 'nav nav-pills');

        $menu->addChild('All users',    ['route' => 'cms_admin_user']);
        $menu->addChild('Create user',  ['route' => 'cms_admin_user_create']);
        $menu->addChild('Roles',        ['route' => 'cms_admin_user_roles']);

        return $menu;
    }

    /**
     * Меню управления стуктурой (папки и блоки).
     *
     * @param FactoryInterface $factory
     * @param array $options
     *
     * @return ItemInterface
     */
    public function structure(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('admin_structure');

        $menu->setChildrenAttribute('class', isset($options['class']) ? $options['class'] : 'nav nav-pills');
        $menu->addChild('Create folder',  ['route' => 'cms_admin_structure_folder_create']);
        $menu->addChild('Connect module', ['route' => 'cms_admin_structure_node_create']);
        $menu->addChild('Regions',        ['route' => 'cms_admin_structure_region']);
        $menu->addChild('Trash',          ['route' => 'cms_admin_structure_trash']);

        return $menu;
    }

    /**
     * Меню на странице редактирования папки.
     *
     * @param FactoryInterface $factory
     * @param array $options
     */
    public function structureOnFolderEdit(FactoryInterface $factory, array $options)
    {
        $menu = $this->structure($factory, $options);

        $item = $menu->addChild('Folder edit', ['uri' => '#']);
        $item->setCurrent(true);

        return $menu;
    }

    /**
     * Меню на странице редактирования ноды.
     *
     * @param FactoryInterface $factory
     * @param array $options
     */
    public function structureOnNodeEdit(FactoryInterface $factory, array $options)
    {
        $menu = $this->structure($factory, $options);

        $item = $menu->addChild('Node edit', ['uri' => '#']);
        $item->setCurrent(true);

        return $menu;
    }

    /**
     * Построение полной структуры, включая ноды.
     *
     * @param FactoryInterface  $factory
     * @param array             $options
     *
     * @return ItemInterface
     */
    public function structureTree(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('full_structure');
        $menu->setChildrenAttributes([
            'class' => 'filetree',
            'id'    => 'browser',
        ]);

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
        $folders = (null == $parent_folder)
            ? $this->container->get('cms.folder')->findByParent(null)
            : $parent_folder->getChildren();

        /** @var $folder Folder */
        foreach ($folders as $folder) {
            if ($folder->isDeleted()) {
                continue;
            }

            $uri = $this->container->get('router')->generate('cms_admin_structure_folder', ['id' => $folder->getId()]);

            if ($folder->isActive()) {
                $title = $folder->getTitle();
            } else {
                $title = '<span style="text-decoration: line-through;">'.$folder->getTitle().'</span>';
            }

            $menu->addChild($folder->getTitle(), ['uri' => $uri])
                ->setAttributes([
                    'class' => 'folder',
                    'title' => $folder->getDescription(),
                    'id'    => 'folder_id_'.$folder->getId(),
                ])
                ->setLabel($title)
            ;

            /** @var $sub_menu ItemInterface */
            $sub_menu = $menu[$folder->getTitle()];

            $this->addChild($sub_menu, $folder);

            /** @var $node \SmartCore\Bundle\CMSBundle\Entity\Node */
            foreach ($folder->getNodes() as $node) {
                if ($node->isDeleted()) {
                    continue;
                }

                $title = $node->getDescription().' ('.$node->getModule().':'.$node->getId().')';

                if ($node->isNotActive()) {
                    $title = '<span style="text-decoration: line-through;">'.$title.'</span>';
                }

                $uri = $this->container->get('router')->generate('cms_admin_structure_node_properties', ['id' => $node->getId()]);
                $sub_menu
                    ->addChild($node->getId(), ['uri' => $uri])
                    ->setAttributes([
                        'title' => $node->getDescription(),
                        'id'    => 'node_id_'.$node->getId(),
                    ])
                    ->setLabel($title)
                ;
            }
        }
    }
}
