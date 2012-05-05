<?php

namespace SmartCore\Bundle\EngineBundle\Templater\Engine\Twig;

class Twig
{
	protected $template;
	
	public function __construct($options = null)
	{		
		$twig = new \Twig_Environment(new Loader\Filesystem($options['paths']), $options['environment']);
		$this->template = $twig->loadTemplate($options['template'] . $options['template_ext']);
	}
	
	public function display($properties)
	{
		$this->template->display($properties);
	}
	
	public function render($properties)
	{
		return $this->template->render($properties);
	}
}