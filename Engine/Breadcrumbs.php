<?php 

namespace SmartCore\Bundle\EngineBundle\Engine;

class Breadcrumbs //  extends View
{
	/**
	 * Массив с хлебными крошками.
	 */
	protected $breadcrumbs = array();
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		//parent::__construct(array('action' => 'defaultRender'));
	}
	
	/**
	 * Установить данные.
	 */
	public function assign($data)
	{
		$this->breadcrumbs = $data;
	}
	
	/**
	 * Добавление хлебной крошки.
	 * 
	 * @param string $uri
	 * @param string $title
	 * @param string $descr
	 */
	public function add($uri, $title, $descr = false)
	{
		$this->breadcrumbs[] = array(
			'uri'	=> $uri,
			'title' => $title,
			'descr' => $descr,
			);
	}
	
	/**
	 * Получиить хлебные крошки.
	 * 
	 * @return array
	 */
	public function get($num = false)
	{
		// @todo если $num отрицательный, то вернуть указанный номер с конца, напроимер -1 это последний, а -2 предпослений и т.д...
		
		$data = array();
		$current_uri = '';
		foreach ($this->breadcrumbs as $key => $value) {
			$data[$key] = $value;
			if (cmf_is_absolute_path($value['uri'])) {
				$current_uri = $value['uri'];
				continue;
			} else {
				$current_uri .= $value['uri'];
				$data[$key]['uri'] = $current_uri;
			}
		}
		
		if ($num === false) {
			return $data;
		} else {
			return $data[$num];
		}
	}
	
	/**
	 * Получение ссылки на последнюю крошку.
	 */
	public function getLastUri()
	{
		$item = $this->get(count($this->breadcrumbs) - 1);
		return $item['uri'];
	}
	
	/**
	 * Отрисовщик по умолчанию.
	 */
	public function defaultRender()
	{
		$bc = $this->get();
		$cnt = count($bc);
		if ($cnt > 0) {
			foreach ($bc as $item) {
				echo --$cnt ? "<a href=\"{$item['uri']}\" title=\"{$item['descr']}\">" : '';
				echo $item['title'];
				echo $cnt ? "</a>&nbsp;&raquo;&nbsp;" : '';
			}
			echo "\n";
		}
	}
}