<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use SmartCore\Bundle\EngineBundle\Templater\View;

class Controller extends BaseController
{
	/**
	 * View object
	 * @var View
	 */
	public $View;

	/**
	 * Constructor.
	 * 
	 * Вызывается как parent::__construct(); из дочерних классов.
	 */
	public function __construct()
	{
		//$this->View = $this->Templating->getView();
		$this->View	= new View();
	}	
	
	protected function init()
	{
		$this->container->get('engine.db')->getConfiguration()->setSQLLogger($this->container->get('db.logger'));
	}
	
	/**
	 * Магическое обращение к сервисам
	 *
	 * @param
	 */
	public function __get($name)
	{
		if (!is_object($this->container)) {
			throw new \Exception('SmartCore\EngineBundle: Container is not accesible. Service "engine.' . $name . '" fail.');
		}
		
		if ($this->container->has('engine.' . $name)) {
			return $this->container->get('engine.' . $name);
		} else {
			throw new \Exception('SmartCore\EngineBundle: Service "engine.' . strtolower($name) . '" does not register.');
		}
	}
}