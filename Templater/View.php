<?php

namespace SmartCore\Bundle\EngineBundle\Templater;

class View
{
	/**
	 * Опции.
	 * @var array
	 */
	protected $__options = array();

	/**
	 * Constructor.
	 *
	 * @param array $options
	 */
	public function __construct(array $options = array())
	{
		$this->__options = array(
			'comment'	=> null,			// Служебный комментарий
			'engine'	=> 'twig',			// Шаблонный движок.
			'environment'	=> array(),		// Окружение для шаблонного движка.
			'paths'		=> array(),         // Пути в которых ищется файл шаблона.
			'template'	=> null,			// Путь к файлу шаблона.
			'template_ext'	=> '.twig',		// Расширение имени файла шаблона. @todo
			'method'	=> 'includeTpl',	// echoProperties - метод вызываемый для отрисовки.
			'decorators'		=> null,	// Декораторы - отображаются до и после рендеринга.
			'properties_count'	=> 0,		// Счетчик свойств. @todo скорее всего убрать.
			//'controller'=> false, // $this
			);
		$this->__options = $options + $this->__options;
	}
	
	/**
	 * Утановить опции.
	 *
	 * @param array $options
	 */
	public function setOptions(array $options = array())
	{
		$this->__options = $options + $this->__options;
	}
	
	/**
	 * Получить глобальные пути в которых производится поиск шаблонов.
	 * 
	 * @return array
	 */
	public function getPaths()
	{
		return $this->__options['paths'];
	}
	
	/**
	 * Установить глобальные пути в которых производится поиск шаблонов.
	 * 
	 * @param array $paths
	 */
	public function setPaths($paths)
	{
		$this->__options['paths'] = $paths;
	}
	
	/**
	 * Добавить глобальный путь в конец списка.
	 * 
	 * @param string $path
	 */
	public function appendPath($path)
	{
		$this->__options['paths'][] = $path;
	}
		
	/**
	 * Добавить глобальный путь в начало списка.
	 * 
	 * @param string $path
	 */
	public function prependPath($path)
	{
		array_unshift($this->__options['paths'], $path);
	}
		
	/**
	 * Отобразить все свойства.
	 */
	public function echoProperties()
	{
		foreach ($this as $property => $__dummy) {
			if ($property == '__options' or $property === '__properties') {
				continue;
			}
			
			echo $this->$property;
		}
	}

	/**
	 * Получить данные свойств.
	 * @return array
	 */
	public function all()
	{
		$properties = array();
		foreach ($this as $property => $data) {
			if ($property === '__options' or $property === '__properties') {
				continue;
			}			
			$properties[$property] = $data;
		}
		return $properties;		
	}
	
	/**
	 * Получить имя шаблона, по умолчанию при инициализации модуля устанавливается такое же как
	 * имя класса, но в результате работы, модуль может установить другой шаблон.
	 * 
	 * @access public
	 * @final
	 * @return string
	 */
	final public function getTemplateName()
	{
		return $this->__options['template'];
	}	
	
	/**
	 * NewFunction
	 */
	public function setRenderMethod($method)
	{
		$this->__options['method'] = $method;
	}
	
	/**
	 * NewFunction
	 */
	public function setDecorators($before, $after)
	{
		$this->__options['decorators'] = array($before, $after);
	}
	
	/**
	 * NewFunction
	 */
	public function setTemplateName($name)
	{
		$this->__options['template'] = $name;
	}
	
	/**
	 * Установка свойства.
	 * 
	 * @param string $name
	 * @param string $value
	 */
	public function set($name, $value)
	{
		$this->__options['properties_count']++;
		$this->$name = $value;
	}
	
	/**
	 * Магическая установка свойства.
	 * 
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value)
	{
		$this->set($name, $value);
	}
	
	/**
	 * Получить свойство.
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function get($name)
	{
		return isset($this->$name) ? $this->$name : null;
	}
	
	/**
	 * Магическое чтение свойства.
	 *
	 * @param string $name
	 * @return string|null
	 */
	public function __get($name)
	{
		return $this->get($name);
	}
	
	/**
	 * Проверить существует ли свойство.
	 *
	 * @param $name
	 * @return bool
	 */
	public function has($name)
	{
		return isset($this->$name) ? true : false;
	}
	
	/**
	 * Отрисовка формы.
	 * @return text
	 */
	public function __toString()
	{
		ob_start();
		$this->display();
		return ob_get_clean();
	}

	/**
	 * Базовый метод отрисовки шаблона с помощью включения файла шаблона.
	 */
	public function includeTpl()
	{
		if (empty($this->__options['paths'])) {
			throw new \Exception('Не указаны пути для шаблонов.');
		}
		
		if (empty($this->__options['template'])) {
			throw new \Exception('Не указано имя шаблона.');
		}
		
		switch (strtolower($this->__options['engine'])) {
			case 'twig':
				$template = new Engine\Twig\Twig($this->__options);
				break;
			case 'simple':
				$template = new Engine\Simple\Simple($this->__options);
				break;
			default;
				throw new \Exception('Неопознанный шаблонный движок.');
		}
		
		$template->display($this->all());
	}
	
	/**
	 * Отображение данных вида.
	 */
	public function display()
	{		
		if (!empty($this->__options['decorators'])) {
			echo $this->__options['decorators'][0];
		}
		
		$this->{$this->__options['method']}();
		
		if (!empty($this->__options['decorators'])) {
			echo $this->__options['decorators'][1];
		}
	}
}
