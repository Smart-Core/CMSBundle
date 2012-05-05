<?php

namespace SmartCore\Bundle\EngineBundle\Templater\Engine\Simple;

class Simple
{
	protected $__options;
	
	public function __construct($options = null)
	{	
		$this->__options = $options;
	}
	
	public function display($properties)
	{
		if (is_array($properties)) {
			foreach ($properties as $key => $value) {
				$this->$key = $value;
			}
		}
		
		$tpl = '/' . $this->__options['template'] . $this->__options['template_ext'];
		
		foreach ($this->__options['paths'] as $path) {
			if (file_exists($path . $tpl)) {
				include $path . $tpl;
				break;
			}
		}
	}
}