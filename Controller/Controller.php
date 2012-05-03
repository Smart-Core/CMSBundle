<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
	protected $DB;
	
	public function __construct()
	{
	}
	
	public function init()
	{
		$this->DB = $this->container->get('db');
		$this->DB->getConfiguration()->setSQLLogger($this->container->get('db.logger'));
	}
}