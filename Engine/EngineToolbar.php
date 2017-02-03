<?php

namespace SmartCore\Bundle\CMSBundle\Engine;

use SmartCore\Bundle\CMSBundle\Entity\Node;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EngineToolbar extends Controller
{
    /**
     * Получить массив с данными для генерации тулбара.
     *
     * @return array
     */
    public function getArray()
    {
        $current_folder_id  = $this->get('cms.context')->getCurrentFolderId();
        $router             = $this->get('router');
        $t                  = $this->get('translator');

        // @todo кеширование по языку и юзеру.
        $data = [
            'left' => [
                'administration' => [
                    'title' => $t->trans('Administration'),
                    'descr' => '',
                    'icon' => 'admin',
                    'onlyicon' => true,
                    'uri'   => $router->generate('cms_admin_index'),
                ],
                'menu' => [
                    'title' => $t->trans('Menu'),
                    'descr' => '',
                    'icon' => 'menu',
                    'items' => [
                        'structure' => [
                            'title' => $t->trans('Site structure'),
                            'icon'  => 'structure',
                            'uri'   => $router->generate('cms_admin_structure'),
                        ],
                        'modules' => [
                            'title' => $t->trans('Modules'),
                            'icon'  => 'modules',
                            'uri'   => $router->generate('cms_admin_module'),
                        ],
                        'files' => [
                            'title' => $t->trans('Files'),
                            'icon'  => 'file',
                            'uri'   => $router->generate('cms_admin_files'),
                        ],
                        'regions' => [
                            'title' => $t->trans('Regions'),
                            'icon'  => 'area',
                            'uri'   => $router->generate('cms_admin_structure_region'),
                        ],
                        'users' => [
                            'title' => $t->trans('Users'),
                            'icon'  => 'users',
                            'uri'   => $router->generate('cms_admin_user'),
                        ],
                        'config' => [
                            'title' => $t->trans('Configuration'),
                            'icon'  => 'conf',
                            'uri'   => $router->generate('smart_core_settings'),
                        ],
                        'appearance' => [
                            'title' => $t->trans('Appearance'),
                            'icon'  => 'code',
                            'uri'   => $router->generate('cms_admin_appearance'),
                        ],
                        'reports' => [
                            'title' => $t->trans('Reports'),
                            'icon' => 'report',
                            'uri' => $router->generate('cms_admin_reports'),
                        ],
                        /*
                        'help' => [
                            'title' => $t->trans('Help'),
                            'icon' => 'question-sign',
                            'uri' => $router->generate('cms_admin_help'),
                        ],
                        */
                    ],
                ],
                'structure' => [
                    'title' => '',
                    'descr' => $t->trans('Structure'),
                    'icon'  => 'folder',
                    'onlyicon' => true,
                    'items' => [
                        'folder_edit' => [
                            'title' => $t->trans('Edit folder'),
                            'icon'  => 'edit-toolbar',
                            'uri'   => $router->generate('cms_admin_structure_folder', ['id' => $current_folder_id]),
                        ],
                        'folder_new' => [
                            'title' => $t->trans('Create folder'),
                            'icon'  => 'add',
                            'uri'   => $router->generate('cms_admin_structure_folder_create_in_folder', ['parent' => $current_folder_id]),
                        ],
                        'add_module' => [
                            'title' => $t->trans('Add module'),
                            'icon'  => 'add',
                            'uri'   => $router->generate('cms_admin_structure_node_create_in_folder', ['folder_pid' => $current_folder_id]),
                        ],
                    ],
                ],
            ],
            'right' => [
                //'eip_toggle' => ['Режим правки: ОТКЛ', 'Режим правки: ВКЛ.'], // @todo перевод // [$t->trans('Viewing'), $t->trans('Edit')],
                'user' => [
                    'title' => $this->container->get('security.token_storage')->getToken()->getUser()->getUserName(),
                    'icon' => 'user-setting',
                    'items' => [
                        /*
                        'admin' => [
                            'title' => $t->trans('Control panel'),
                            'uri'   => $router->generate('cms_admin_index'),
                            'icon'  => 'cog',
                            'overalay' => false,
                        ],
                        */
                        'profile' => [
                            'title' => $t->trans('My profile'),
                            'uri'   => $router->generate('cms_admin_user_edit', ['id' => $this->container->get('security.token_storage')->getToken()->getUser()->getId()]),
                            'icon'  => 'profile',
                            'overalay' => false,
                        ],
                        'logout' => [
                            'title' => $t->trans('Logout'),
                            'uri'   => $router->generate('cms_admin_logout'),
                            'icon'  => 'exit',
                            'overalay' => false,
                        ],
                    ],
                ],
            ],
            'notifications' => [],
        ];

        foreach ($this->get('cms.node')->getNodes() as $node) {
            if ($node->getControlsInToolbar() == Node::TOOLBAR_ONLY_IN_SELF_FOLDER) {
                foreach ($node->getFrontControls() as $controls) {
                    if (isset($controls['default']) and $controls['default'] == true) {
                        $data['left']['node_'.$node->getId()] = [
                            'title' => $controls['title'],
                            'descr' => isset($controls['descr']) ? $controls['descr'] : '',
                            'uri'   => $controls['uri'],
                        ];
                    }
                }
            }
        }

        foreach ($this->get('cms.module')->all() as $module) {
            $notices = $module->getNotifications();

            if (!empty($notices)) {
                $data['notifications'][$module->getName()] = $notices;
            }
        }

        return $data;
    }

    /**
     * @param array $nodes_front_controls
     */
    public function prepare(array $nodes_front_controls = null)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $cms_front_controls = [
                'twitterBootstrapVersion' => $this->get('settings')->get('cms:twitter_bootstrap_version'),
                'toolbar' => $this->getArray(),
                'nodes'   => $nodes_front_controls,
            ];

            $this->get('smart.felib')
                //->call('bootstrap')
                ->call('jquery-cookie');

            $this->get('html')
                ->css($this->get('templating.helper.assets')->getUrl('bundles/cms/css/frontend.css'))
                ->js($this->get('templating.helper.assets')->getUrl('bundles/cms/js/frontend.js'))
                ->appendToHead('<script type="text/javascript">var cms_front_controls = '.json_encode($cms_front_controls, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT).';</script>');
        }
    }
}
