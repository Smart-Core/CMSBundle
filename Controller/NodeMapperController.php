<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SmartCore\Bundle\EngineBundle\Container;
use SmartCore\Bundle\EngineBundle\Engine\View;

class NodeMapperController extends Controller
{
    public function indexAction(Request $request, $slug)
    {
        // @todo вынести router в другое место... можно сделать в виде отдельного сервиса, например 'engine.folder_router'.
        $router_data = $this->get('engine.folder')->router($request->getPathInfo());
        //ld($router_data);

        /** @var $folder \SmartCore\Bundle\EngineBundle\Entity\Folder */
        foreach ($router_data['folders'] as $folder) {
            $this->get('engine.breadcrumbs')->add($folder->getUri(), $folder->getTitle(), $folder->getDescr());
            if ($router_data['node_route']['response']) {
                foreach ($router_data['node_route']['response']->getBreadcrumbs() as $bc) {
                    $this->get('engine.breadcrumbs')->add($bc['uri'], $bc['title'], $bc['descr']);
                }
            }
        }

        $this->View->setOptions(array(
            'comment'   => 'Базовый шаблон',
            'template'  => $router_data['template'],
        ));

        $this->get('html')->title('Smart Core CMS (based on Symfony2 Framework)');

        // @todo убрать в ini-шник шаблона.
        $this->get('html')->meta('viewport', 'width=device-width, initial-scale=1.0');

        if ($this->get('security.context')->isGranted('ROLE_ADMIN') && !$request->isXmlHttpRequest()) {
            $cmf_front_controls = array(
                'toolbar' => array(
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
                                    'uri' => $request->getBasePath() . '/admin/structure/folder/edit/2/',
                                ),
                                'folder_new' => array(
                                    'title' => 'Добавить раздел',
                                    'icon' => 'plus',
                                    'uri' => $request->getBasePath() . '/admin/structure/folder/create/2/',
                                ),
                                'folder_all' => array(
                                    'title' => 'Вся структура',
                                    'icon' => 'book',
                                    'uri' => $request->getBasePath() . '/admin/structure/folder/',
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
                ),
                'node' => array(
                    '__node_3' => array(
                        'edit' => array(
                            'title' => 'Редактировать',
                            'descr' => 'Текстовый блок',
                            'uri' => $request->getBasePath() . '/admin/structure/node/3/',
                            'default' => true,
                        ),
                        'cmf_node_properties' => array(
                            'title' => 'Свойства ноды',
                            'uri' => $request->getBaseUrl() . '/',
                        ),
                    ),
                    '__node_1' => array(
                        'edit' => array(
                            'title' => 'Редактировать',
                            'descr' => 'Текстовый блок',
                            'uri' => $request->getBasePath() . '/',
                            'default' => true,
                        ),
                        'cmf_node_properties' => array(
                            'title' => 'Свойства ноды',
                            'uri' => $request->getBaseUrl() . '/',
                        ),
                    ),
                    '__node_5' => array(
                        'edit' => array(
                            'title' => 'Редактировать',
                            'descr' => 'Меню',
                            'uri' => $request->getBasePath() . '/',
                            'default' => true,
                        ),
                        'add' => array(
                            'title' => 'Добавить пункт меню',
                            'uri' => $request->getBasePath() . '/',
                        ),
                        'cmf_node_properties' => array(
                            'title' => 'Свойства ноды',
                            'uri' => $request->getBaseUrl() . '/',
                        ),
                    ),
                    '__node_6' => array(
                        'edit' => array(
                            'title' => 'Редактировать',
                            'descr' => 'Хлебные крошки',
                            'uri' => $request->getBasePath() . '/',
                            'default' => true,
                        ),
                        'cmf_node_properties' => array(
                            'title' => 'Свойства ноды',
                            'uri' => $request->getBaseUrl() . '/',
                        ),
                    ),
                    '__node_2' => array(
                        'cmf_node_properties' => array(
                            'title' => 'Свойства ноды',
                            'uri' => $request->getBaseUrl() . '/',
                            'default' => true,
                        ),
                    ),
                ),
            );

            $this->engine('JsLib')->request('bootstrap');
            $this->engine('JsLib')->request('jquery-cookie');
            $this->get('html')
                ->css($this->engine('env')->global_assets . 'cmf/frontend.css')
                ->js($this->engine('env')->global_assets . 'cmf/frontend.js')
                ->js($this->engine('env')->global_assets . 'cmf/jquery.ba-hashchange.min.js')
                // @todo продумать как называть "general_data".
                ->general_data = '<script type="text/javascript">var cmf_front_controls = ' . json_encode($cmf_front_controls, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . ';</script>'
            ;
        }

        $theme_path = $this->get('engine.env')->theme_path;
        $this->View->assets = array(
            'theme_path'        => $theme_path,
            'theme_css_path'    => $theme_path . 'css/',
            'theme_js_path'     => $theme_path . 'js/',
            'theme_img_path'    => $theme_path . 'images/',
            'vendor'            => $this->engine('env')->global_assets,
        );

        $this->engine('theme')->processConfig($this->View);

        foreach ($this->engine('JsLib')->all() as $res) {
            if (isset($res['js']) and is_array($res['js'])) {
                foreach ($res['js'] as $js) {
                    $this->get('html')->js($js, 200);
                }
            }
            if (isset($res['css']) and is_array($res['css'])) {
                foreach ($res['css'] as $css) {
                    $this->get('html')->css($css, 200);
                }
            }
        }

        $this->View->set('blocks', new View(array(
            'comment'   => 'Блоки',
            'engine'    => 'echo',
        )));

        $nodes_list = $this->get('engine.node_manager')->buildNodesList($router_data);
//        ld($nodes_list);

        $this->buildModulesData($nodes_list);

//        ld($this->View->blocks);
//        ld($this->renderView("Menu::menu.html.twig", array()));
//        ld($this->forward('Texter:Test:hello', array('text' => 'yahoo :)'))->getContent());
//        ld($this->forward('2:Test:index')->getContent());

//        $tmp = $this->forward(8);
//        $tmp = $this->forward('MenuModule:Menu:index');
//        ld(get_class($tmp));
//        ld($tmp->getContentRaw());
//        echo $tmp->getContent();

        /*
        $activeTheme = $this->get('liip_theme.active_theme');
        $activeTheme->setThemes(array('web', 'tablet', 'phone'));
        $activeTheme->setName('phone');
        */

        return new Response($this->container->get('templating')->render("::{$this->View->getTemplateName()}.html.twig", array(
                'block' => $this->View->blocks,
            )),
            $router_data['status']
        );
    }
    
    /**
     * Сборка "блоков" из подготовленного списка нод.
     * По мере прохождения, подключаются и запускаются нужные модули с нужными параметрами.
     */
    protected function buildModulesData($nodes_list)
    {
        define('_IS_CACHE_NODES', false); // @todo remove

        /** @var $node \SmartCore\Bundle\EngineBundle\Entity\Node */
        foreach ($nodes_list as $node_id => $node) {
            $block_name = $node->getBlock()->getName();

            if (!$this->View->blocks->has($block_name)) {
                $this->View->blocks->set($block_name, new View());
            }

            // Обнаружены параметры кеша.
            if (_IS_CACHE_NODES and $node['is_cached'] and !empty($node['cache_params']) and $this->engine('env')->cache_enable ) {
                $cache_params = unserialize($node['cache_params']);
                if (isset($cache_params['id']) and is_array($cache_params['id'])) {
                    $cache_id = array();
                    foreach ($cache_params['id'] as $key => $dummy) {
                        switch ($key) {
                            case 'current_folder_id':
                                $cache_id['current_folder_id'] = $this->engine('env')->current_folder_id;
                                break;
                            case 'user_id':
                                $cache_id['user_id'] = $this->engine('env')->user_id;
                                break;
                            case 'parser_data': // @todo route_data
                                $cache_id['parser_data'] = $node_properties['parser_data'];
                                break;
                            case 'request_uri':
                                $cache_id['parser_data'] = $_SERVER['REQUEST_URI'];
                                break;
                            case 'user_groups':
                                $user_data = $this->User->getData();
                                $cache_id['user_groups'] = $user_data['groups'];
                                break;
                            default;
                        }
                    }
                    $cache_params['id'] = $cache_id;
                }
                $cache_params['id']['node_id'] = $node_id;
                $cache_params['nodes'][$node_id] = 1;
            } else {
                $cache_params = null;
            }

            // Попытка взять HTML кеш ноды.
            if (_IS_CACHE_NODES
                and !empty($cache_params)
                and $this->Cookie->sc_frontend_mode !== 'edit'
                and $html_cache = $this->Cache_Node->loadHtml($cache_params['id'])
            ) {
                // $this->EE->data[$block_name][$node_id]['html_cache'] = $html_cache; @todo !!!!!!!!
            }
            // Кеша нет.
            else { 
                // Если разрешены права на запись ноды, то создаётся объект с административными методами и запрашивается у него данные для фронтальных элементов управления.
                /*
                if ($this->Permissions->isAllowed('node', 'write', $node_properties['permissions']) and ($this->Permissions->isRoot() or $this->Permissions->isAdmin()) ) {
                    $Module = $Node->getModuleInstance($node_id, true);
                } else {
                    $Module = $Node->getModuleInstance($node_id, false);
                }
                */

                // Выполняется модуль, все параметры ноды берутся в SmartCore\Bundle\EngineBundle\Listener\ModuleControllerModifier
                $Module = $this->forward($node_id, array(
                    '_eip' => true,
                ));

                // Указать шаблонизатору, что надо сохранить эту ноду как html.
                // @todo ПЕРЕДЕЛАТЬ!!! подумать где выполнять кеширование, внутри объекта View или где-то снаружи.
                // @todo ВАЖНО подумать как тут поступить т.к. эта кука может стоять у гостя!!!
                if (_IS_CACHE_NODES and !empty($cache_params) and $this->Cookie->sc_frontend_mode !== 'edit') {
//                    $this->EE->data[$block_name][$node_id]['store_html_cache'] = $Module->getCacheParams($cache_params);
                } 

                // Получение данных для фронт-админки ноды.
                // @todo сделать нормальную проверку на возможность управления нодой. сейчас пока считается, что юзер с ИД = 1 имеет право админить.
                // @todo также тут надо учитывать режим Фронт-Админки. если он выключен, то вытягивать фронт-контролсы нет смысла.
                
                //if ($this->Permissions->isAllowed('node', 'write', $node_properties['permissions']) and $this->Cookie->sc_frontend_mode == 'edit') {
                if ( false ) {
                    $front_controls = $Module->getFrontControls();
                    
                    // Для рута добавляется пунктик "свойства ноды"
                    if ($this->Permissions->isRoot()) {
                        $front_controls['_node_properties'] = array(
                            'popup_window_title' => 'Свойства ноды' . " ( $node_id )",
                            'title'              => 'Свойства',
                            'link'               => HTTP_ROOT . ADMIN . '/structure/node/' . $node_id . '/?popup',
                            'ico'                => 'edit',
                        );
                    }

                    if(is_array($front_controls)) {
                        // @todo сделать выбор типа фронт админки popup/built-in/ajax.
                        $this->View->admin['frontend'][$node_id] = array(
                            // 'type' => 'popup',
                            'node_action_mode'  => $node_properties['node_action_mode'],
                            'doubleclick'       => '@todo двойной щелчок по блоку НОДЫ.',
                            'default_action'    => $Module->getFrontControlsDefaultAction(),
                            // элементы управления, относящиеся ко всей ноде.
                            'controls'          => $front_controls,
                            // элементы управления блоков внутри ноды.
                            //'controls_inner_default_action' = $Module->getFrontControlsInnerDefaultAction(),
                            'controls_inner'    => $Module->getFrontControlsInner(),
                        );
                    }

                    $Module->View->setDecorators("<div class=\"cmf-frontadmin-node\" id=\"_node$node_id\">", "</div>");
                }
            }

            if (method_exists($Module, 'getContentRaw')) {
                $this->View->blocks->$block_name->$node_id = $Module->getContentRaw();
            } else {
                $this->View->blocks->$block_name->$node_id = $Module->getContent();
            }

            // @todo пока так выставляются декораторы обрамления ноды.
            if ($this->get('security.context')->isGranted('ROLE_ADMIN') && !$this->get('request')->isXmlHttpRequest()) {
                //ld($this->View->blocks->$block_name->$node_id);
                if ($this->View->blocks->$block_name->$node_id instanceof View) {
                    $this->View->blocks->$block_name->$node_id->setDecorators("<div class=\"cmf-frontadmin-node\" id=\"__node_{$node_id}\">", "</div>");
                }
            }

            unset($Module);
        }
    }
}
