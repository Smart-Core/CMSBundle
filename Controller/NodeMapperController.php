<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use SmartCore\Bundle\EngineBundle\Engine\Theme;
use SmartCore\Bundle\EngineBundle\Templater\View;

class NodeMapperController extends Controller
{
	/**
	 * Свойcтво поведения действия над нодой (всроенное в шаблон - 'built-it' или во всплывающем окошке - 'pupup' или 'ajax' - подгружаемое в блок размещения.).
	 * По умолчанию инициализируется как false.
	 * 
	 * @access private
	 * 
	 * @todo надо подумать, надо ли вообще это держать в кернеле?
	 */	
	protected $front_end_action_mode = false;
	
	/**
	 * @access private
	 * 
	 * @todo надо подумать, надо ли вообще это держать в кернеле?
	 */
	protected $front_end_action_node_id = 0;



	protected $template;
	protected $meta;
	protected $status;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->template	= 'index';
		$this->meta		= array();
		$this->status	= 200;
	}

	public function indexAction($slug)
	{
		$this->init();

//		sc_dump($user = $this->container->get('security.context')->getToken()->getUser());
//		sc_dump($this->container->getParameterBag());
//		sc_dump($this->container->getParameter('security.role_hierarchy.roles'));
		
		/*
		if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')) {
//			echo "123<br />";
		}
		*/
		
		$folders = $this->router($this->get('request')->getPathInfo());
		
//		sc_dump($folders);
		
		foreach ($folders as $folder) {
			$this->Breadcrumbs->add($folder['uri'], $folder['title'], $folder['descr']);
		}	
		
		$this->View->setOptions(array(
			'comment'	=> 'Базовый шаблон',
			//'engine'	=> 'twig',
			'template'	=> $this->template,
			'environment' => array(
				'cache'			=> $this->Env->dir_cache . 'twig',
				'auto_reload'	=> true,
				'autoescape'	=> false,
				//'debug'			=> true,
			),
		));
		
		$reflector = new \ReflectionClass('SmartCore\Bundle\EngineBundle\SmartCoreEngineBundle');
		$this->View->setPaths(array(
			$this->Env->dir_web_root . 'theme/views', // @todo сделать через настройки.
			$this->Env->dir_app . 'Resources/views',
			dirname($reflector->getFileName()) . '/Resources/views',
		));
		
		$this->View->html = $this->Html;
		$this->Html->title('SC Framework!');

		$this->View->assets = array(
			'theme_path'		=> $this->Env->base_path . $this->Env->theme_path,
			'theme_css_path'	=> $this->Env->base_path . $this->Env->theme_path . 'css/',
			'theme_js_path'		=> $this->Env->base_path . $this->Env->theme_path . 'js/',
			'theme_img_path'	=> $this->Env->base_path . $this->Env->theme_path . 'images/',
			'vendor'			=> $this->Env->global_assets,
		);
		
//		$Theme = new Theme();
		$this->Theme->processConfig($this->View);
		
		foreach ($this->JsLib->all() as $lib => $res) {
			if (isset($res['js']) and is_array($res['js'])) {
				foreach ($res['js'] as $js) {
					$this->Html->js($js, 200);
				}
			}
			if (isset($res['css']) and is_array($res['css'])) {
				foreach ($res['css'] as $css) {
					$this->Html->css($css, 200);
				}
			}
		}
		
		$this->View->block = new View();
		//$this->View->block->setRenderMethod('echoProperties');
		$this->View->block->setOptions(array('comment' => 'Блоки'));
		
		$nodes_list = $this->buildNodesList($folders);
		
//		sc_dump($nodes_list);
		
		$this->buildModulesData($nodes_list);
		
//		sc_dump($this->View->block);
//		sc_dump($this->Html);
    
        $View = $this->container->get('templating')->render("::{$this->View->getTemplateName()}.html.twig", array(
			'html' => $this->Html,
            'block' => $this->View->block,
		));
        
        return new Response($View, $this->status);
        
//		sc_dump($this->Breadcrumbs);
		
//		sc_dump($data);
//		sc_dump($this->get('engine.env'));
//		sc_dump($this->Env);
//		sc_dump($this->Site);
//		sc_dump($this->Site->getProperties(1));
//		sc_dump($this->Folder);
		
//		sc_dump(BASE_PATH);
		
//		sc_dump($this->get('engine.site'));
//		sc_dump($this->getUser());
		
		return new Response($this->View, $this->status);
	}
	
	/**
	 * Роутинг.
	 * 
	 * @param string $slug
	 * @return array
	 */
	protected function router($slug)
	{
		$folders = array();
		
		$current_folder_path = $this->Env->get('base_url');
		$router_node_id = null;
		$folder_pid = 0;
//		$Folder = new Folder();

		$path_parts = explode('/', $slug);
		
		foreach ($path_parts as $key => $segment) {
			// Проверка строки запроса на допустимые символы.
			// @todo сделать проверку на разрешение круглых скобок.
			if (!empty($segment) and !preg_match('/^[a-z_@0-9.-]*$/iu', $segment)) {
				$this->status = 404;
				break;
			}
			
			// заканчиваем работу, если имя папки пустое и папка не является корневой 
			// т.е. обрабатываем последнюю запись в строке УРИ
			if('' == $segment and 0 != $key) { 
				// @todo видимо здесь надо делать обработчик "файла" т.е. папки с выставленным флагом "is_file".
				break;
			}
			
			// В данной папке есть нода которой передаётся дальнейший парсинг URI.
			if ($router_node_id !== null) {
				// выполняется часть URI парсером модуля и возвращается результат работы, в дальнейшем он будет передан самой ноде.
				// @todo сделать создание объекта модуля через статический вызов Ноде.
//				$Node = new Node();
				$Module = $this->Node->getModuleInstance($router_node_id);
				$module_route = $Module->router(str_replace($current_folder_path, '', substr($this->Env->base_path, 0, -1) . $slug));
				unset($Module);
				
				// Парсер модуля вернул положительный ответ.
				if ($module_route !== false) {
					$folders[$folder->folder_id]['route'] = $module_route;
					$folders[$folder->folder_id]['route']['node_id'] = $router_node_id;
					// В случае успешного завершения роутера модуля, роутинг ядром прекращается.
					break; 
				}				
			} // __end if ($router_node_id !== null)
						
			$folder = $this->Folder->getData($segment, $folder_pid);
			
			if ($folder !== false) {
				//if ($this->Permissions->isAllowed('folder', 'read', $folder->permissions)) {
				if ( true ) {
					// Заполнение мета-тегов.
					if (!empty($folder->meta)) {
						foreach (unserialize($folder->meta) as $key2 => $value2) {
							$this->meta[$key2] = $value2;
						}
					}
					
					if ($folder->uri_part !== '') {
						$current_folder_path .= $folder->uri_part . '/';
					}
					
					// Чтение макета для папки.
					// @todo возможно ненадо. оставить только один view.
					if (!empty($folder->layout)) {
						$this->template = $folder->layout;
					}
					
					$folder_pid		= $folder->folder_id;
					$router_node_id = $folder->router_node_id;
					$folders[$folder->folder_id] = array(
						'uri'	=> $current_folder_path,
						'title'	=> $folder->title,
						'descr'	=> $folder->descr,
						'is_inherit_nodes' => $folder->is_inherit_nodes,
						'lockout_nodes'	 => unserialize($folder->lockout_nodes),
					);
					$this->Env->set('current_folder_id', $folder->folder_id);
					$this->Env->set('current_folder_path', $current_folder_path);
				} else {
					$this->status = 403;
				}
			} else {
				$this->status = 404;
			}
		}
		
		return $folders;
	}
	
	/**
	 * Создание списка всех запрошеных нод, в каких блоках они находятся и с какими 
	 * параметрами запускаются модули.
	 * 
	 * @access protected
	 * 
	 * @param array 	$parsed_uri
	 * @return array 	$nodes_list
	 */
	protected function buildNodesList(array $folders)
	{
		$nodes_list = array();
		
		// @todo не собирать список нод, если сработан механизи ACTIONS во всплывающем окошке.
		if ($this->front_end_action_mode === 'popup') {
			return;	
		}

		$lockout_nodes = array(
			'single'  => array(), // Блокировка нод в папке, без наследования.
			'inherit' => array(), // Блокировка нод в папке, с наследованием.
			'except'  => array(), // Блокировка всех нод в папке, кроме заданных.
			);
		$used_nodes = array();
		
		foreach ($folders as $folder_id => $parsed_uri_value) {
			// single каждый раз сбрасывается и устанавливается заново для каждоый папки.
			$lockout_nodes['single'] = array();
			if (isset($parsed_uri_value['lockout_nodes']['single']) and !empty($parsed_uri_value['lockout_nodes']['single'])) {
				//$lockout_nodes['single'] = $parsed_uri_value['lockout_nodes']['single'];
				$tmp = explode(',', $parsed_uri_value['lockout_nodes']['single']);
				foreach ($tmp as $single_value) {
					$t = trim($single_value);
					if (!empty($t)) {
						$lockout_nodes['single'][trim($single_value)] = 'blocked'; // ставлю тупо 'blocked', но главное в массиве с блокировками, это индексы.
					}
				}
			}
			
			// Блокировка нод в папке, с наследованием.
			if (isset($parsed_uri_value['lockout_nodes']['inherit']) and !empty($parsed_uri_value['lockout_nodes']['inherit'])) {
				$tmp = explode(',', $parsed_uri_value['lockout_nodes']['inherit']);
				foreach ($tmp as $inherit_value) {
					$t = trim($inherit_value);
					if (!empty($t)) {
						$lockout_nodes['inherit'][trim($inherit_value)] = 'blocked'; // ставлю тупо 'blocked', но главное в массиве с блокировками, это индексы.
					}
				}
			}
			
			// Блокировка всех нод в папке, кроме заданных.
			if (isset($parsed_uri_value['lockout_nodes']['except']) and !empty($parsed_uri_value['lockout_nodes']['except'])) {
				$tmp = explode(',', $parsed_uri_value['lockout_nodes']['except']);
				foreach ($tmp as $except_value) {
					$t = trim($except_value);
					if (!empty($t)) {
						$lockout_nodes['except'][trim($except_value)] = 'blocked'; // ставлю тупо 'blocked', но главное в массиве с блокировками, это индексы.
					}
				}
			}
			
			$sql = false;
			if ($parsed_uri_value['is_inherit_nodes'] == 1) { // в этой папке есть ноды, которые наследуются...
				$sql = "SELECT n.module_id, n.node_id, n.params, n.cache_params, n.plugins, n.database_id, n.action,
						n.permissions, n.is_cached, n.block_id AS block_id,	n.node_action_mode
					FROM {$this->DB->prefix()}engine_nodes AS n,
						{$this->DB->prefix()}engine_blocks_inherit AS bi
					WHERE n.block_id = bi.block_id 
						AND is_active = 1
						AND n.folder_id = '{$folder_id}'
						AND bi.folder_id = '{$folder_id}'
						AND n.site_id = '{$this->Site->getId()}'
						AND bi.site_id = '{$this->Site->getId()}'
					ORDER BY n.pos ";
			}
			
			// Обрабатываем последнюю папку т.е. текущую.
			if ($folder_id == $this->Env->current_folder_id) { // @todo убрать Env
				$sql = "SELECT * FROM {$this->DB->prefix()}engine_nodes WHERE folder_id = '{$folder_id}' AND is_active = '1' AND site_id = '{$this->Site->getId()}' ";
				// исключаем ранее включенные ноды.
				foreach ($used_nodes as $used_nodes_value) {
					$sql .= " AND node_id != '{$used_nodes_value}'";
				}
				$sql .= ' ORDER BY pos';
			}
			
			// В папке нет нод для сборки.
			if ($sql === false) {
				continue;
			}

			$result = $this->DB->query($sql);
			while ($row = $result->fetchObject()) {
				/*
				if ($this->Permissions->isAllowed('node', 'read', $row->permissions) == 0) {
					continue;
				}
				*/
				
				// Создаётся список нод, которые уже в включены.
				if ($parsed_uri_value['is_inherit_nodes'] == 1) { 
					$used_nodes[] = $row->node_id; 
				}

				$nodes_list[$row->node_id] = array(
					'folder_id'		=> $folder_id,
					'module_id'		=> $row->module_id,
					'action'		=> $row->action,
					'block_id'		=> $row->block_id,
					'params'		=> $row->params,
					'cache_params'	=> $row->cache_params,
					'is_cached'		=> $row->is_cached,
					'plugins'		=> $row->plugins,
					'permissions'	=> $row->permissions,
					'route_params'	=> null, // В случае, если не был отработан механизм парсинга строки запроса модулем, считаеся, что парсер данных не вернул ничего либо вернул NULL.
					'database_id'	=> $row->database_id,
					'node_action_mode' => $row->node_action_mode,
					);
			}
			
			if (isset($parsed_uri_value['route'])) {
				$nodes_list[$parsed_uri_value['route']['node_id']]['route_params'] = $parsed_uri_value['route'];
			}
		}
		
		foreach ($lockout_nodes['single'] as $node_id => $value) {
			unset($nodes_list[$node_id]);
		}
		
		foreach ($lockout_nodes['inherit'] as $node_id => $value) {
			unset($nodes_list[$node_id]);
		}
		
		if (!empty($lockout_nodes['except'])) {
			foreach ($nodes_list as $node_id => $value) {
				if (!array_key_exists($node_id, $lockout_nodes['except'])) {
					unset($nodes_list[$node_id]);
				}
			}
		}
		
		return $nodes_list;
	}	
	
	/**
	 * Собираютя блоки из подготовленного списка нод,
	 * по мере прохождения, подключаются и запускаются нужные модули с нужными параметрами.
	 * 
	 * @uses Module_*
	 */
	protected function buildModulesData($nodes_list)
	{
		$blocks = array();
		
		// Создаётся список всех доступных блоков в системе.
		$sql = "SELECT block_id, name
			FROM {$this->DB->prefix()}engine_blocks
			WHERE site_id = '{$this->Site->getId()}'
			ORDER BY pos ASC ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$blocks[$row->block_id] = $row->name;
			$name = $row->name;
			$this->View->block->$name = new View();
			$this->View->block->$name->setRenderMethod('echoProperties');
		}
		
		define('_IS_CACHE_NODES', false); // @todo remove
		
//		$Node = new Node();
		foreach ($nodes_list as $node_id => $node_properties) {
			// Не собираем ноду, если она уже была отработала в механизе nodeAction()
			if ($node_id == $this->front_end_action_node_id) {
				continue;
			}
			
//			$this->profilerStart('node', $node_id);
			
			$block_name = $blocks[$node_properties['block_id']];

			// Обнаружены параметры кеша.
			if (_IS_CACHE_NODES and $node_properties['is_cached'] and !empty($node_properties['cache_params']) and $this->Env->cache_enable ) {
				$cache_params = unserialize($node_properties['cache_params']);
				if (isset($cache_params['id']) and is_array($cache_params['id'])) {
					$cache_id = array();
					foreach ($cache_params['id'] as $key => $dummy) {
						switch ($key) {
							case 'current_folder_id':
								$cache_id['current_folder_id'] = $this->Env->current_folder_id;
								break;
							case 'user_id':
								$cache_id['user_id'] = $this->Env->user_id;
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
				
				$Module = $this->Node->getModuleInstance($node_id, false);
				if (empty($node_properties['route_params'])) {
					$Module->{$node_properties['action'] . 'Action'}($node_properties['route_params']);
				} else {
					if (isset($node_properties['route_params']['params'])) {
						$Module->{$node_properties['route_params']['action'] . 'Action'}($node_properties['route_params']['params']);
					} else {
						$Module->{$node_properties['route_params']['action'] . 'Action'}();
					}
				}
				
				// Указать шаблонизатору, что надо сохранить эту ноду как html.
				// @todo ПЕРЕДЕЛАТЬ!!! подумать где выполнять кеширование, внутри объекта View или где-то снаружи.
				// @todo ВАЖНО подумать как тут поступить т.к. эта кука может стоять у гостя!!!
				if (_IS_CACHE_NODES and !empty($cache_params) and $this->Cookie->sc_frontend_mode !== 'edit') {
//					$this->EE->data[$block_name][$node_id]['store_html_cache'] = $Module->getCacheParams($cache_params);
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
							'title'				 => 'Свойства',
							'link'				 => HTTP_ROOT . ADMIN . '/structure/node/' . $node_id . '/?popup',
							'ico'				 => 'edit',
						);
					}

					if(is_array($front_controls)) {
						// @todo сделать выбор типа фронт админки popup/built-in/ajax.
						$this->View->admin['frontend'][$node_id] = array(
							// 'type' => 'popup',
							'node_action_mode'	=> $node_properties['node_action_mode'],
							'doubleclick'		=> '@todo двойной щелчок по блоку НОДЫ.',
							'default_action'	=> $Module->getFrontControlsDefaultAction(),
							// элементы управления, относящиеся ко всей ноде.
							'controls'			=> $front_controls,
							// элементы управления блоков внутри ноды.
							//'controls_inner_default_action' = $Module->getFrontControlsInnerDefaultAction(),
							'controls_inner'	=> $Module->getFrontControlsInner(),
							);
					}
					
					// @todo пока так выставляются декораторы обрамления ноды.
					$Module->View->setDecorators("<div class=\"cmf-frontadmin-node\" id=\"_node$node_id\">", "</div>");
				}
			}
			
			$this->View->block->$block_name->$node_id = $Module->View;

//			$this->profilerStop('node', $node_id);
			unset($Module);
		}
		unset($Node);
	}
}