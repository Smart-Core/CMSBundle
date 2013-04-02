<?php

namespace SmartCore\Bundle\EngineBundle\Engine;

use Symfony\Component\DependencyInjection\ContainerAware;

class Toolbar extends ContainerAware
{
    public function getArray()
    {
        $base_path = $this->container->get('request')->getBasePath(); // @todo remove
        $current_folder_id = $this->container->get('engine.env')->get('current_folder_id');

        /** @var $router \Symfony\Bundle\FrameworkBundle\Routing\Router */
        $router = $this->container->get('router');

        $username = $this->container->get('security.context')->getToken()->getUser()->getUserName();

        return array(
            'left' => array(
                'setings' => array(
                    'title' => '',
                    'descr' => 'Настройки',
                    'icon' => 'wrench',
                    'items' => array(
                        'blocks' => array(
                            'title' => 'Блоки',
                            'icon' => 'th',
                            'uri' => $router->generate('cmf_admin_structure_block'),
                        ),
                        'appearance' => array(
                            'title' => 'Оформление',
                            'icon' => 'picture',
                            'uri' => $router->generate('cmf_admin_appearance'),
                        ),
                        'users' => array(
                            'title' => 'Пользователи',
                            'icon' => 'user',
                            'uri' => $router->generate('cmf_admin_users'),
                        ),
                        'modules' => array(
                            'title' => 'Модули',
                            'icon' => 'cog',
                            'uri' => $router->generate('cmf_admin_module'),
                        ),
                        'config' => array(
                            'title' => 'Конфигруация',
                            'icon' => 'tasks',
                            'uri' => $router->generate('cmf_admin_config'),
                        ),
                        'reports' => array(
                            'title' => 'Отчеты',
                            'icon' => 'warning-sign',
                            'uri' => $router->generate('cmf_admin_reports'),
                        ),
                        'help' => array(
                            'title' => 'Справка',
                            'icon' => 'question-sign',
                            'uri' => $router->generate('cmf_admin_help'),
                        ),
                    ),
                ),
                'structure' => array(
                    'title' => 'Структура',
                    'descr' => '',
                    'icon' => 'folder-open',
                    'items' => array(
                        'folder_edit' => array(
                            'title' => 'Редактировать раздел',
                            'icon' => 'edit',
                            'uri' => $router->generate('cmf_admin_structure_folder', array('id' => $current_folder_id)),
                        ),
                        'folder_new' => array(
                            'title' => 'Добавить раздел',
                            'icon' => 'plus',
                            'uri' => $router->generate('cmf_admin_structure_folder_create_in_folder', array('folder_pid' => $current_folder_id)),
                        ),
                        'folder_all' => array(
                            'title' => 'Вся структура',
                            'icon' => 'book',
                            'uri' => $router->generate('cmf_admin_structure'),
                        ),
                        'diviver_1' => 'diviver',
                        'node_new' => array(
                            'title' => 'Добавить модуль',
                            'icon' => 'plus',
                            'uri' => $router->generate('cmf_admin_structure_node_create_in_folder', array('folder_pid' => $current_folder_id)),
                        ),
                        'node_all' => array(
                            'title' => 'Все модули на странице @todo',
                            'icon' => 'list-alt',
                            'uri' => $base_path . '/admin/structure/node/in_folder/2/',
                        ),
                    ),
                ),
            ),
            'right' => array(
                'eip_toggle' => array("Просмотр", "Редактирование"),
                'user' => array(
                    'title' => $username,
                    'icon' => 'user',
                    'items' => array(
                        'profile' => array(
                            'title' => 'Мой профиль',
                            'uri' => $router->generate('fos_user_profile_show'),
                            'icon' => 'cog',
                            'overalay' => true,
                        ),
                        'diviver_1' => 'diviver',
                        'logout' => array(
                            'title' => "Выход",
                            'uri' => $router->generate('fos_user_security_logout'),
                            'icon' => "off",
                            'overalay' => false,
                        ),
                    ),
                ),
            ),
        );
    }
}
