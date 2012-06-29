<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use SmartCore\Bundle\EngineBundle\Controller\Controller;
use SmartCore\Bundle\EngineBundle\Engine\NodeProperties;
 
abstract class Module extends Controller
{
	/**
	 * Действие по умолчанию.
	 * @access protected
	 * @var string|false
	 */
	protected $default_action = false;
	
	/**
	 * Фронтальные элементы управления для всего модуля.
	 * @access protected
	 * @var array|false
	 */
	protected $frontend_controls = false;
	
	/**
	 * Фронтальные элементы управления для внутренних элементов модуля.
	 * @access protected
	 * @var array|false
	 */
	protected $frontend_inner_controls = false;
	
	/**
	 * Свойства ноды.
	 * @var object
	 */
	protected $Node;
	
	/**
	 * Базовый конструктор. Модули используют в качестве конструктора метод init();
	 * 
	 * @access public
	 * @param int $node_id
	 */
	final public function __construct($container = null, $node_id = false)
	{
        if ($container) {
            $this->setContainer($container);
        }
		
		parent::__construct();
		
		if ($node_id === false) {
			// @todo сообщение о недопустимой операции.
			return null;
		}
		
		$this->NodeProperties = new NodeProperties($node_id, $container);
		
		// По умолчанию устанавливается имя шаблона, как короткое имея класса, но без префикса 'Module_' и без постфикса '_Admin'.
		$reflector = new \ReflectionClass(get_class($this));
		$namespace = str_replace('\Controller', '', $reflector->getNamespaceName());
		$this->View->setTemplateName($reflector->getShortName());
		$this->View->setPaths(array(
			$this->Env->dir_web_root . $this->Env->dir_themes . 'views/' . $namespace,
			$this->Env->dir_app . 'views/' . $namespace,
			realpath(dirname($reflector->getFileName()) . '/../Resources/views'),
		));
        
		// При database_id = 0 модуль будет использовать тоже подключение, что и ядро, иначе создаётся новое подключение.
		if ($this->NodeProperties->database_id != 0) {
			// @todo для совместимости с эмуляцией функции get_called_class для РНР 5.2, дальше для PHP 5.3 only можно будет записывать в одну строку, без $con_data.
			$db_key = 'DB.' . $this->Node->database_id;
			if (!Registry::has($db_key)) {
				$con_data = $this->DB_Resources->getConnectionData($this->Node->database_id);
				Registry::set($db_key, DB::connect($con_data));
			}
			$this->DB = Registry::get($db_key);
			unset($con_data, $db_key);
		}
		
		// Запуск метода init(), который является заменой конструктора для модулей.
		if (method_exists($this, 'init')) {
			$this->init();
			foreach ($this->NodeProperties->getParams() as $key => $value) {
				$this->$key = $value;
			}
		}
	}

	/**
	 * Ajax.
	 *
	 * @param string $uri_path - часть URI, адресованная модулю.
	 * @return ?
	 */
	public function ajax($uri_path)
	{
		return null;
	}
	
	// Ниже описаны административные методы, они должны быть описаны в классе Module_*_Admin.

	/**
	 * Метод управления модулем.
	 *
	 * @param string $uri_path
	 * @return array
	 */
	public function admin($uri_path)
	{
		return false;
	}
	
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @access public
	 * @return array $params
	 */
	public function getParams()
	{
		return array();
	}
	
	/**
	 * Получить параметры кеширования модуля.
	 * 
	 * @access public
	 * @return array $params
	 */
	public function getCacheParams($cache_params = array())
	{
		$params = array();
		foreach ($cache_params as $key => $value) {
			$params[$key] = $value;
		}
		return $params;
	}
	
	/**
	 * Вызывается при создании ноды.
	 * 
	 * @access public
	 * @return array $params
	 */
	public function createNode()
	{
		$params = $this->Node->getDefaultParams();
		return empty($params) ? 'NULL' : $params;
	}
	
	/**
	 * Метод-заглушка, для модулей, которые не имеют фронт администрирования. 
	 * Возвращает пустой массив или null или 0, следовательно движок ничего не отображает.
	 * 
	 * @access public
	 * @returns array|false
	 * 
	 * @todo определиться какое значение лучше возвращать 0 или false.
	 */
	public function getFrontControls()
	{
		return $this->frontend_controls;
	}
	
	/**
	 * Внутренние элменты управления ноды.
	 * 
	 * @access public
	 * @returns array|false
	 */
	public function getFrontControlsInner()
	{
		return $this->frontend_inner_controls;
	}
	
	/**
	 * Действие по умолчанию.
	 * 
	 * @access public
	 * @returns string|false
	 */
	public function getFrontControlsDefaultAction()
	{
		return $this->default_action;
	}
	
	/**
	 * Выполнение задач по расписанию.
	 *
	 * @access public
	 * @return bool|null
	 */
	public function cron()
	{
		return null;
	}
}