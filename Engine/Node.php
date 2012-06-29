<?php

namespace SmartCore\Bundle\EngineBundle\Engine;

use SmartCore\Bundle\EngineBundle\Controller\Controller;
use SmartCore\Bundle\EngineBundle\Container;

class Node extends Controller
{
	/**
	 * Node ID.
	 * @var int
	 */
	protected $node_id = false;
	
	/**
	 * Объект модуля.
	 * @var object
	 */
	protected $Module;
	
	/**
	 * Constructor.
	 *
	 * @param
	 * @return
	 */
	public function __construct($node_id = false)
	{
		parent::__construct();
		if ($node_id) {
			$this->activate($node_id);
		}
	}
	
	/**
	 * Активировать ноду.
	 *
	 * @param int $node_id
	 * 
	 * @todo может быть и не нужно...
	 */
	public function activate($node_id)
	{
		$this->node_id = $node_id;
	}
	
	/**
	 * Получить свойва ноды.
	 *
	 * @param int $node_id
	 * @param string $property - получить конкретное свойство, если не указано, возвращаются все свойства.
	 * @return array|string
	 */
    public function getProperties($node_id, $property = false)
    {
        $sql = "SELECT engine_nodes.*, engine_modules.class
            FROM {$this->DB->prefix()}engine_nodes
            LEFT JOIN {$this->DB->prefix()}engine_modules USING (module_id)
            WHERE node_id = '$node_id'
            AND site_id = '{$this->container->get('engine.site')->getId()}' ";
        $result = $this->DB->query($sql);
		if ($result->rowCount() == 1) {
			$row = $result->fetchObject();
			
			if ($property === false) {
				$properties = array(
					'is_active'		=> $row->is_active,
					'folder_id'		=> $row->folder_id,
					'module_id'		=> $row->module_id,
					'action'		=> $row->action,
					'module_class'	=> $row->class,
					'block_id'		=> $row->block_id,
					'route_params'	=> false,
					'cache_params'	=> empty($row->cache_params) ? null : unserialize($row->cache_params),
					'params'		=> empty($row->params) ? array() : unserialize($row->params),
					'permissions'	=> $row->permissions,
					'plugins'		=> $row->plugins,
					'database_id'	=> $row->database_id,
					'descr'			=> $row->descr,
				);
                
				return $properties;
			} else {
				return $row->$property;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Получить объект модуля.
	 *
	 * @param int $node_id
	 * @param bool $is_admin - вернуть объект с административными методами.
	 * @return object
	 */
	public function getModuleInstance($node_id = false, $is_admin = false)
	{
		/**
		  Array
			(
				[folder_id] => 1
				[module_id] => Texter
				[block_id] => 3
				[params] => a:1:{s:12:"text_item_id";s:1:"1";}
				[permissions] => 
				[database_id] => 0
				[node_action_mode] => popup
				[session] => 0
			)
		 */
		/*
		$properties = Kernel::getNodeData($node_id);
		
		if ($properties === null) {
			$properties = $this->getProperties($node_id);
		}
		*/
		
		$properties = $this->getProperties($node_id);
		
		if ($properties === null) {
			return null;
		}

//		$class = 'Module_' . $properties['module_class'];
		$class = $properties['module_class'];
		if ($is_admin) {
			$class .= '_Admin';
		}

		return new $class($this->container->get('service_container'), $node_id);
	}
	
	/**
	 * Создание новой ноды.
	 *
	 * @param array $pd
	 * @return bool
	 */
	public function __create($pd) // @todo 
	{
		if (is_numeric($pd['block_id'])) {
			$block_id = $pd['block_id'];
		} else {
			return false;
		}

		// Вычисление максимальной позиции, чтобы поместить новую ноду в конец внутри блока.
		/*
		$sql = "SELECT max(pos) as max_pos 
			FROM {$this->DB->prefix()}engine_nodes
			WHERE block_id = '$pd[block_id]'
			AND folder_id = '$pd[folder_id]'
			AND site_id = '{$this->Env->site_id}' ";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		$max_pos = $row->max_pos + 1;
		*/
		$pos			= is_numeric($pd['pos']) ? $pd['pos'] : 0;
		$database_id	= (isset($pd['database_id']) and is_numeric($pd['database_id'])) ? $pd['database_id'] : 0;
		$is_active		= (isset($pd['is_active']) and is_numeric($pd['is_active'])) ? $pd['is_active'] : 1;
		$is_cached		= is_numeric($pd['is_cached']) ? $pd['is_cached'] : 1;
		$folder_id		= is_numeric($pd['folder_id']) ? $pd['folder_id'] : 1;
		$permissions	= strlen(trim($pd['permissions'])) == 0 ? 'NULL' : $this->DB->quote(trim($pd['permissions']));
		
		$descr = $this->DB->quote(trim($pd['descr']));
		$sql = "
			INSERT INTO {$this->DB->prefix()}engine_nodes
				(folder_id, site_id, descr, block_id, module_id, database_id, params, is_active, is_cached, pos, permissions, create_datetime, owner_id)
			VALUES
				('$folder_id', '{$this->Env->site_id}', $descr, '$block_id', '$pd[module_id]', '$database_id', NULL, '$is_active', '$is_cached', '$pos', $permissions, NOW(), '{$this->Env->user_id}') ";
		$this->DB->query($sql);
		$node_id = $this->DB->lastInsertId();
		
		$Node = new Node();
		$Module = $Node->getModuleInstance($node_id, true);
		$params = $Module->createNode();
		
		if ($params != 'NULL') {
			$params = "'" . serialize($params) . "'";
		}
		
		$sql = "
			UPDATE {$this->DB->prefix()}engine_nodes SET
				params = $params
			WHERE node_id = '$node_id'
			AND site_id = '{$this->Env->site_id}' ";
		$this->DB->exec($sql);
		$this->Cache->updateFolder($folder_id);
        
		return true;
	}
	
	/**
	 * Получить список всех нод.
	 *
	 * @param
	 * @return array
	 * 
	 * @todo постраничность.
	 */
	public function getList($items_per_page = false, $page_num = 1)
	{
		return $this->getListInFolder();
	}
	
	/**
	 * Получить список нод в папке.
	 * 
	 * @param int $folder_id - если false, то возвращается список всех нод.
	 * @return array
	 */
	public function getListInFolder($folder_id = false)
	{
		$sql_folder = $folder_id === false ? '' : " AND folder_id = '$folder_id' ";
		
		$nodes = array();
		$sql = "SELECT n.node_id, n.block_id, n.folder_id, n.pos, n.module_id, n.action,
				n.params, n.plugins, n.is_cached, n.is_active, n.database_id, 
				n.descr, b.name AS block_name, b.descr AS block_descr
			FROM {$this->DB->prefix()}engine_nodes AS n
			LEFT JOIN {$this->DB->prefix()}engine_blocks AS b USING (block_id)
			WHERE n.site_id = '{$this->Env->site_id}'
			AND b.site_id = '{$this->Env->site_id}'
			$sql_folder
			ORDER BY n.pos ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$nodes[$row->node_id] = array(
				'descr'			=> $row->descr,
				'is_active'		=> $row->is_active,
				'folder_id'		=> $row->folder_id,
				'pos'			=> $row->pos,
				'module_id'		=> $row->module_id,
				'action'		=> $row->action,
				'database_id'	=> $row->database_id,
				'params'		=> $row->params,
				'plugins'		=> $row->plugins,
				'block_name'	=> $row->block_name,
				'block_descr'	=> $row->block_descr,
			);
		}
        
		return $nodes;
	}
	
	/**
	 * Получить список всех нод заданного модуля.
	 *
	 * @param string $module
	 * @return array
	 */
	public function getListByModule($module)
	{
		$data = array();
		$sql = "SELECT node_id 
			FROM {$this->DB->prefix()}engine_nodes
			WHERE site_id = '{$this->Env->site_id}'
			AND module_id = {$this->DB->quote($module)} ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$data[$row->node_id] = $this->getProperties($row->node_id);
		}
        
		return $data;
	}
 
	/**
	 * Обновление параметров ноды.
	 * 
	 * @param int $node_id
	 * @param array $pd
	 * @return bool
	 */
	public function update($node_id, $pd)
	{
		if (is_numeric($pd['block_id'])) {
			$block_id = $pd['block_id'];
		} else {
			return false;
		}
		
		$pos			= is_numeric($pd['pos']) ? $pd['pos'] : 0;
		$database_id	= (isset($pd['database_id']) and is_numeric($pd['database_id'])) ? $pd['database_id'] : 0;
		$is_active		= (isset($pd['is_active']) and is_numeric($pd['is_active'])) ? $pd['is_active'] : 1;
		$is_cached		= is_numeric($pd['is_cached']) ? $pd['is_cached'] : 1;
		$folder_id		= is_numeric($pd['folder_id']) ? $pd['folder_id'] : 1;
		$permissions	= strlen(trim($pd['permissions'])) == 0 ? 'NULL' : $this->DB->quote(trim($pd['permissions']));
		$params			= (!isset($pd['params']) or count($pd['params']) == 0) ? 'params = NULL' : "params = " . $this->DB->quote(serialize($pd['params']));
		$plugins		= strlen(trim($pd['plugins'])) == 0 ? 'NULL' : $this->DB->quote(trim($pd['plugins']));
		$cache_params_yaml	= (isset($pd['cache_params_yaml']) and !empty($pd['cache_params_yaml'])) ? 'cache_params_yaml = ' . $this->DB->quote($pd['cache_params_yaml']) : 'cache_params_yaml = NULL';
		$cache_params	= $cache_params_yaml == 'cache_params_yaml = NULL' ? 'cache_params = NULL' : 'cache_params = ' . $this->DB->quote(serialize(Zend_Config_Yaml::decode($pd['cache_params_yaml'])));
		$descr = $this->DB->quote(trim($pd['descr']));
		
		// @todo action		
		$sql = "
			UPDATE {$this->DB->prefix()}engine_nodes SET
				descr = $descr,
				folder_id = '$folder_id',
				pos = '$pos',
				database_id = '$database_id',
				block_id = '$block_id',
				is_active = '$is_active',
				is_cached = '$is_cached',
				permissions = $permissions,
				plugins = $plugins,
				$params,
				$cache_params,
				$cache_params_yaml
			WHERE
				node_id = '$node_id'
			AND site_id = '{$this->Env->site_id}' ";
		$this->DB->exec($sql);
		$this->Cache->updateNode($node_id);
		return true;
	}
	
	/**
	 * Хуки.
	 *
	 * @param string $method - имя вызываемого метода.
	 * @param array $args - массив с аргументами.
	 * @return mixed
	 */
	public function hook($method, array $args = null)
	{
		$Module = $this->getModuleInstance($this->node_id);
		return is_object($Module) ? $Module->hook($method, $args) : null;
	}
    
    /**
     * Создание списка всех запрошеных нод, в каких блоках они находятся и с какими 
     * параметрами запускаются модули.
     * 
     * @access protected
     * 
     * @param array     $parsed_uri
     * @return array     $nodes_list
     */
    public function buildNodesListByFolders(array $folders)
    {
        $nodes_list = array();
        $used_nodes = array();
        $lockout_nodes = array(
            'single'  => array(), // Блокировка нод в папке, без наследования.
            'inherit' => array(), // Блокировка нод в папке, с наследованием.
            'except'  => array(), // Блокировка всех нод в папке, кроме заданных.
        );

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
                        n.permissions, n.is_cached, n.block_id AS block_id, n.node_action_mode
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
                    'folder_id'         => $folder_id,
                    'module_id'         => $row->module_id,
                    'action'            => $row->action,
                    'block_id'          => $row->block_id,
                    'params'            => $row->params,
                    'cache_params'      => $row->cache_params,
                    'is_cached'         => $row->is_cached,
                    'plugins'           => $row->plugins,
                    'permissions'       => $row->permissions,
                    'route_params'      => null, // В случае, если не был отработан механизм парсинга строки запроса модулем, считаеся, что парсер данных не вернул ничего либо вернул NULL.
                    'database_id'       => $row->database_id,
                    'node_action_mode'  => $row->node_action_mode,
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
}