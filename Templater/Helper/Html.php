<?php

namespace SmartCore\Bundle\EngineBundle\Templater\Helper;

/**
 * @todo поддерку тега <base>
 * @todo document_ready
 * @todo Безопасные скрипты: //<![CDATA[' .... //]]>
 * @todo продумать приоритеты подключения LESS и CSS, а то сейчас LESS подключается только через тег link, а он выводится вперед всех. возможно это и не так важно ;)
 */
class Html
{
	protected $sorted		= array();
	
	public $doctype			= "<!DOCTYPE html>\n<html>";
	public $title			= '';
	public $meta			= array();
	public $styles			= array();
	public $scripts			= array();
	public $links			= array();
	public $document_ready	= '';
	public $general_data	= '';
	public $body_attributes	= array();
	public $lang 			= 'ru';
	public $direction		= 'ltr';	
	public $end				= ' /';	// Завершающий символ. Например для HTML - <br>, а для XHTML - <br />
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->setMetaHttpEquiv('Content-Type', 'text/html; charset=utf-8');
		$this->setMetaHttpEquiv('Content-Language', 'ru');		
	}
	
	/**
	 * Set document type.
	 * 
	 * Здесь же генерация открытия тега <html> с аргументами для доктайпа.
	 * 
	 * @param string $doctype
	 */
	public function setDoctype($doctype = 'HTML5')
	{
		switch ($doctype) {
			case 'XHTML11':
			case 'xhtml11':
				$this->doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
				$this->doctype .= "\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemalocation=\"http://www.w3.org/1999/xhtml http://www.w3.org/MarkUp/SCHEMA/xhtml11.xsd\" xml:lang=\"{$this->lang}\" lang=\"{$this->lang}\" dir=\"{$this->direction}\">";
				$this->end = ' /';
				break;
			case 'XHTML1_STRICT':
			case 'xhtml1-strict':
			case 'xhtml-strict':
				$this->doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
				$this->doctype .= "\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$this->lang}\" lang=\"{$this->lang}\" dir=\"{$this->direction}\">";
				$this->end = ' /';
				break;
			case 'XHTML1_TRANSITIONAL':
			case 'xhtml1-trans':
			case 'xhtml-trans':
				$this->doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
				$this->doctype .= "\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$this->lang}\" lang=\"{$this->lang}\" dir=\"{$this->direction}\">";
				break;
			case 'XHTML1_FRAMESET':
			case 'xhtml1-frame':
			case 'xhtml-frame':
				$this->doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
				$this->doctype .= "\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$this->lang}\" lang=\"{$this->lang}\" dir=\"{$this->direction}\">";
				$this->end = ' /';
				break;
			case 'XHTML1_RDFA':
				$this->doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">';
				$this->doctype .= "\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$this->lang}\" lang=\"{$this->lang}\" dir=\"{$this->direction}\">";
				$this->end = ' /';
				break;
			case 'XHTML_BASIC1':
				$this->doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">';
				$this->doctype .= "\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$this->lang}\" lang=\"{$this->lang}\" dir=\"{$this->direction}\">";
				$this->end = ' /';
				break;
			case 'html4-trans':
			case 'HTML4_LOOSE':
			case 'HTML4_TRANSITIONAL':
				$this->doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
				$this->doctype .= "\n<html>";
				$this->end = '';
				break;
			case 'HTML4_STRICT':
			case 'html4-strict':
				$this->doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
				$this->doctype .= "\n<html>";
				$this->end = '';
				break;
			case 'HTML4_FRAMESET':
			case 'html4-frame':
				$this->doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
				$this->doctype .= "\n<html>";
				$this->end = '';
				break;
			case 'HTML5':
			case 'html5':
				$this->doctype = '<!DOCTYPE html>';
				$this->doctype .= "\n<html>";
				$this->end = ' /';
				break;
			default:
		}
	}

	/**
	 * Добавить Мета-тэг keywords.
	 *
	 * @param string $keyword
	 */
	public function addMetaKeyword($keyword)
	{
		if (isset($this->meta['name']['keywords']) and ! empty($this->meta['name']['keywords'])) {
			$this->meta['name']['keywords'] .= ', ' . $keyword;
		} else {
			$this->setMeta('keywords', $keyword);
		}
	}
	
	/**
	 * Установить Мета-тэг description.
	 *
	 * @param string $descr
	 */
	public function setMetaDescription($descr)
	{
		$this->setMeta('description', $descr);
	}
	
	/**
	 * Добавить атрибут тэга <body>.
	 * 
	 * @param string $attr
	 * @param string $value
	 */
	public function setBodyAttribute($attr, $value)
	{
		$this->body_attributes[$attr] = $value; 
	}
	
	/**
	 * Добавить мета тэг.
	 *
	 * @param string $name
	 * @param string $content
	 * @param string $type (name, http-equiv, property)
	 */
	public function setMeta($name, $content, $type = 'name')
	{
		$this->meta[$type][strtolower($name)] = $content;
	}
	
	/**
	 * Добавить мета тэг http-equiv.
	 *
	 * @param string $name
	 * @param string $content
	 */
	public function setMetaHttpEquiv($name, $content)
	{
		$this->setMeta($name, $content, 'http-equiv');
	}
	
	/**
	 * Добавить мета тэг property.
	 *
	 * @param string $name
	 * @param string $content
	 */
	public function setMetaProperty($name, $content)
	{
		$this->setMeta($name, $content, 'property');
	}
	
	/**
	 * Привязка внешних документов.
	 */
	public function addLink($href, $params = null, $priority = 0)
	{
		$data = array('href' => $href);
		
		if (is_array($params)) {
			$data = $params + $data;
		} elseif (is_numeric($params)) {
			$priority = $params;
		}
		
		ksort($data);
		$this->sorted['links'][$priority][] = $data;
		$this->sort('links');
	}	

	/**
	 * Добавить данные для тега <script>.
	 * 
	 * @param string $input - src или code.
	 * @param array|string $params - параметры. (_code - вставить код между тегами <script> и </script>.)
	 * @param int $priority - позиция (чем больше, чем раньше подключится)
	 */
	public function addScript($input, $params = null, $priority = 0)
	{
		$data = array('type' => 'text/javascript');
		
		if (is_array($params)) {
			$data = $params + $data;
		} elseif (is_numeric($params)) {
			$priority = $params;
		}
		
		$tmp = parse_url($input);
		if (substr($tmp['path'], -3) == '.js') {
			$data['src'] = $input;
		} else {
			$data['_code'] = $input;
		}
		
		ksort($data);
		$this->sorted['scripts'][$priority][] = $data;
		$this->sort('scripts');
	}
	
	/**
	 * Добавить данные для тега <style>.
	 *
	 * @param string $input - href или code.
	 * @param array|string $params - параметры. (_code - вставляет код между тегами <style> и </style>)
	 * @param int $priority - позиция (чем больше, чем раньше подключится)
	 */
	public function addStyle($input, $params = null, $priority = 0)
	{
		$data = array('type' => 'text/css', 'media' => 'all');
		
		if (is_array($params)) {
			$data = $params + $data;
		} elseif (is_numeric($params)) {
			$priority = $params;
		}
		
		$tmp = parse_url($input);
		if (substr($tmp['path'], -4) == '.css') {
			$data['href'] = $input;
		} elseif (substr($tmp['path'], -5) == '.less') {
			$this->addLink($input, array(
				'rel' => 'stylesheet/less',
				'type' => 'text/css',
				'media' => $data['media'],
			));
			return true;
		} else {
			$data['_code'] = $input;
		}
		
		ksort($data);
		$this->sorted['styles'][$priority][] = $data;
		$this->sort('styles');
	}
	
	/**
	 * Добавить JS код, который должен быть исполнен при событии document-ready.
	 * Метод автоматически подключает либу jquery.
	 *
	 * @param text $js_code
	 */
	public function addDocumentReady($js_code)
	{
		$this->document_ready[] = $js_code;
	}
	
	/**
	 * Установить значение тэга <title>
	 * 
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}
	
	/**
	 * Соритировка.
	 * 
	 * @param string $name
	 */
	protected function sort($name)
	{
		$this->$name = array();
		krsort($this->sorted[$name]);
		foreach ($this->sorted[$name] as $key => $value) {
			foreach ($value as $key2 => $value2) {
				array_push($this->$name, $value2);
			}
		}
	}
	
	// -----------------------------------------------------------------------
	// Ниже описаны алиасы на основные методы для сокрашенного синтаксиса.
	// -----------------------------------------------------------------------
	
	public function js($input, $params = null, $priority = 0)
	{
		$this->addScript($input, $params, $priority);
	}
	
	public function css($input, $params = null, $priority = 0)
	{
		$this->addStyle($input, $params, $priority);
	}
	
	public function meta($name, $content, $type = 'name')
	{
		$this->setMeta($name, $content, $type);
	}
	
	public function title($title)
	{
		$this->setTitle($title);
	}
	
	public function description($descr)
	{
		$this->setMetaDescription($descr);
	}
	public function keyword($keyword)
	{
		$this->addMetaKeyword($keyword);
	}
	
	public function keywords($keyword)
	{
		$this->setMeta('keywords', $keyword);
	}
	
	public function link($href, $args = null, $priority = 0)
	{
		$this->addLink($href, $args, $priority);
	}
	
	public function bodyAttr($attr, $value)
	{
		$this->setBodyAttribute($attr, $value);
	}
	
	public function doctype($doctype)
	{
		$this->setDoctype($doctype);
	}
}
