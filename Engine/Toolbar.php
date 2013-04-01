<?php

namespace SmartCore\Bundle\EngineBundle\Engine;

use Symfony\Component\DependencyInjection\ContainerAware;

class Toolbar extends ContainerAware
{
    public function getArray()
    {
        //ld($this->container->get('engine.env')->get('current_folder_id'));

        $request = $this->container->get('request');

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
                            'uri' => $request->getBasePath() . '/admin/structure/blocks/',
                        ),
                        'appearance' => array(
                            'title' => 'Оформление',
                            'icon' => 'picture',
                            'uri' => $request->getBasePath() . '/admin/appearance/',
                        ),
                        'users' => array(
                            'title' => 'Пользователи',
                            'icon' => 'user',
                            'uri' => $request->getBasePath() . '/admin/users/',
                        ),
                        'modules' => array(
                            'title' => 'Модули',
                            'icon' => 'cog',
                            'uri' => $request->getBasePath() . '/admin/module/',
                        ),
                        'config' => array(
                            'title' => 'Конфигруация',
                            'icon' => 'tasks',
                            'uri' => $request->getBasePath() . '/admin/config/',
                        ),
                        'reports' => array(
                            'title' => 'Отчеты',
                            'icon' => 'warning-sign',
                            'uri' => $request->getBasePath() . '/admin/reports/',
                        ),
                        'help' => array(
                            'title' => 'Справка',
                            'icon' => 'question-sign',
                            'uri' => $request->getBasePath() . '/admin/help/',
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
                            'uri' => $this->container->get('router')->generate('cmf_admin_structure_folder', array(
                                'id' => $this->container->get('engine.env')->get('current_folder_id'))
                            ),
                        ),
                        'folder_new' => array(
                            'title' => 'Добавить раздел',
                            'icon' => 'plus',
                            'uri' => $this->container->get('router')->generate('cmf_admin_structure_folder_create_in_folder', array(
                                'folder_pid' => $this->container->get('engine.env')->get('current_folder_id'))
                            ),
                        ),
                        'folder_all' => array(
                            'title' => 'Вся структура',
                            'icon' => 'book',
                            'uri' => $this->container->get('router')->generate('cmf_admin_structure'),
                        ),
                        'diviver_1' => 'diviver',
                        'node_new' => array(
                            'title' => 'Добавить модуль',
                            'icon' => 'plus',
                            'uri' => $request->getBasePath() . '/admin/structure/node/create/2/',
                        ),
                        'node_all' => array(
                            'title' => 'Все модули на странице',
                            'icon' => 'list-alt',
                            'uri' => $request->getBasePath() . '/admin/structure/node/in_folder/2/',
                        ),
                    ),
                ),
            ),
            'right' => array(
                'eip_toggle' => array("Просмотр", "Редактирование"),
                'user' => array(
                    'title' => $this->container->get('security.context')->getToken()->getUser()->getUserName(),
                    'icon' => 'user',
                    'items' => array(
                        'profile' => array(
                            'title' => 'Мой профиль',
                            'uri' => $this->container->get('router')->generate('fos_user_profile_show'),
                            'icon' => 'cog',
                            'overalay' => true,
                        ),
                        'diviver_1' => 'diviver',
                        'logout' => array(
                            'title' => "Выход",
                            'uri' => $this->container->get('router')->generate('fos_user_security_logout'),
                            'icon' => "off",
                            'overalay' => false,
                        ),
                    ),
                ),
            ),
        );
    }
}
