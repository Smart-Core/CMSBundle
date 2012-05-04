<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
//use SmartCore\Bundle\EngineBundle\Engine\Folder;

class NodeMapperController extends Controller
{
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

		$data = array();
		$sql = "SELECT * FROM text_items ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$data[$row->item_id] = $row->text;
		}
		
		$folders = $this->router($this->get('request')->getPathInfo());
		
		cmf_dump($folders);
		
//		cmf_dump($data);
//		cmf_dump($this->get('engine.env'));
//		cmf_dump($this->Env);
//		cmf_dump($this->Site);
//		cmf_dump($this->Site->getProperties(1));
//		cmf_dump($this->Folder);
		
//		cmf_dump(BASE_PATH);
		
//		cmf_dump($this->get('engine.site'));
//		cmf_dump($this->getUser());
		
		return new Response("Hello $slug !", $this->status);
	}
	
	/**
	 * Роутинг.
	 * 
	 * @param string $slug
	 * @return array
	 */
	public function router($slug)
	{
		$folders = array();
		
		$current_folder_path = $this->Env->get('base_path');
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
				$Node = new Node();
				$Module = $Node->getModuleInstance($router_node_id);
				$module_route = $Module->router(str_replace($current_folder_path, '', substr(HTTP_ROOT, 0, -1) . $slug));
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
}