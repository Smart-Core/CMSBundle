<?php

namespace SmartCore\Bundle\EngineBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class AdminMenu extends ContainerAware
{
    public function main(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('admin_main');

        if (isset($options['class'])) {
            $menu->setChildrenAttribute('class', $options['class']);
        } else {
            $menu->setChildrenAttribute('class', 'nav');
        }

        $menu->addChild('Структура',    array('route' => 'cmf_admin_structure'));
        $menu->addChild('Оформление',   array('route' => 'cmf_admin_appearance'));
        $menu->addChild('Пользователи', array('route' => 'cmf_admin_users'));
        $menu->addChild('Модули',       array('route' => 'cmf_admin_module'));
        $menu->addChild('Конфигруация', array('route' => 'cmf_admin_config'));
        $menu->addChild('Отчеты',       array('route' => 'cmf_admin_reports'));
        $menu->addChild('Справка',      array('route' => 'cmf_admin_help'));

        return $menu;
    }

    public function structure(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('admin_structure');

        if (isset($options['class'])) {
            $menu->setChildrenAttribute('class', $options['class']);
        } else {
            $menu->setChildrenAttribute('class', 'nav nav-pills');
        }

        $menu->addChild('Добавить раздел',      array('route' => 'cmf_admin_structure_folder_create'));
        $menu->addChild('Подключить модуль',    array('route' => 'cmf_admin_structure_node_create'));
        $menu->addChild('Блоки',                array('route' => 'cmf_admin_structure_block'));

        return $menu;
    }
}
