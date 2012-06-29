<?php

namespace SmartCore\Bundle\EngineBundle\Engine;
use SmartCore\Bundle\EngineBundle\Container;

class NodeProperties
{
	/**
	 * Node ID.
	 * @var int
	 */
	public $id;
	
	public $database_id;
	public $folder_id;
	public $module_id;
	public $block_id;
	public $cache_params;
	public $params = array();
	public $permissions;
	public $plugins;
	public $route_params;
	// @todo подумать как лучше поступать с $node_folder_path т.к. он нужен не всегда, но для его генерации нужно затрачивать ресурсы. задача № 200.
	public $node_folder_path_todo;
	
	/**
	 * Значения параметров по умолчанию.
	 * @var array
	 */
	private $__default_params;
	
	/**
	 * Зарегистрированные события.
	 */
	private $__events = null;
	
	protected $container;
	
	/**
	 * Constructor.
	 *
	 * @param array $properties
	 */
	public function __construct($node_id)
	{
		//$properties = Kernel::getNodeData($node_id);
		$properties = null; // @todo 
		
		if ($properties === null) {
			$properties = Container::get('engine.node')->getProperties($node_id);
		}
		
		$this->id			= $node_id;
		$this->database_id	= $properties['database_id'];
		$this->folder_id	= $properties['folder_id'];
		$this->module_id	= $properties['module_id'];
		$this->block_id		= $properties['block_id'];
//        $this->params        = empty($properties['params']) ? array() : unserialize($properties['params']);
		$this->params		= $properties['params'];
		$this->plugins		= $properties['plugins'];
		$this->route_params	= $properties['route_params'];
		$this->permissions	= $properties['permissions'];
//        $this->cache_params = empty($properties['cache_params']) ? null : unserialize($properties['cache_params']);
		$this->cache_params = $properties['cache_params'];
	}
	
	/**
	 * Устновить значения параметров по умолчанию.
	 *
	 * @param array $params
	 */
	final public function setDefaultParams($params)
	{
		$this->__default_params = $params;
	}

	/**
	 * Добавить значение параметра по умолчанию.
	 *
	 * @param array $params
	 * @param string $val
	 */
	final public function addDefaultParam($param, $val)
	{
		$this->__default_params[$param] = $val;
	}
	
	/**
	 * Получить значения параметров по умолчанию.
	 *
	 * @return array
	 */
	final public function getDefaultParams()
	{
		return $this->__default_params;
	}
	
	/**
	 * Получить значение параметра подключения модуля.
	 *
	 * @param string $parm
	 * @return mixed
	 */
	public function getParam($param)
	{
		if (isset($this->params[$param])) {
			return $this->params[$param];
		} elseif (isset($this->__default_params[$param])) {
			return $this->__default_params[$param];
		} else {
			return null;
		}
	}
	
	/**
	 * Получени всех параметров ноды.
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}
	
	/**
	 * Обработчик событий.
	 *
	 * @param string $name
	 * @return array $args
	 */
	public function event($name, array $args = null)
	{
		if (!is_array($this->__events)) {
			$this->__events = array();
			$tmp = explode(';', $this->plugins);
			foreach ($tmp as $plugin) {
				if (strlen($plugin) == 0) {
					continue;
				}
				$class_name = 'Plugin_' . $plugin;
				if (file_exists(Site::getDirApplication() . 'Plugins/'. $plugin . '/' . $plugin . '.php')) {
					$PluginClass = new $class_name();
					$events_list = $PluginClass->__getEventsList();
					foreach ($events_list as $event => $method) {
						$this->__events[$event] = array(
							'class'  => $class_name,
							'method' => $method,
							);
					}
				}
			}
		}
		
		if (array_key_exists($name, $this->__events)) {
			$class_name = $this->__events[$name]['class'];
			$method_name = $this->__events[$name]['method'];
			$PluginClass = new $class_name();
			return $PluginClass->$method_name($args);
		}
		
		return null;
	}
	
	/**
	 * Получить ссылку на папку, где находится нода.
	 *
	 * @param int $node_id
	 * @return string
	 */
	public function getUri($node_id = null)
	{
		if ($node_id === null) {
			$node_id = $this->id;
		}
		
		$DB = Registry::get('DB');
		
		$sql = "SELECT folder_id
			FROM " . $DB->prefix() . "engine_nodes
			WHERE node_id = '{$node_id}' 
			AND site_id = '" . Env::getInstance()->site_id . "' ";
		return Folder::getUri($DB->fetchObject($sql)->folder_id);
	}
}