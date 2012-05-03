<?php
/**
 * Блоки.
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 * @license	http://opensource.org/licenses/gpl-2.0
 * 
 * @uses	DB
 * 
 * @version 2012-02-01.0
 */
class Block extends Container
{
	private $block_list = array();
	
	/**
	 * Получить список блоков.
	 * 
	 * @param int $site_id
	 * @return array
	 */
	public function getList($site_id = false)
	{
		$data = array();
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}engine_blocks
			WHERE site_id = '{$this->Env->site_id}'
			ORDER BY pos ASC";
		$result = $this->DB->query($sql);
		while($row = $result->fetchObject()) {
			$data[$row->block_id] = array(
				'name'		=> $row->name,
				'descr'		=> $row->descr,
				'pos'		=> $row->pos,
				'site_id'	=> $row->site_id,
				'create_datetime'	=> $row->create_datetime,
				'owner_id'	=> $row->owner_id,
				);
		}
		return $data;
	}
	
	/**
	 * Получить массив для применения в Zend_Form multiOptions
	 * 
	 * @return array
	 */
	public function getHtmlSelectOptionsArray()
	{
		if (count($this->block_list) == 0) {
			$this->block_list = $this->getList();
		}
		
		$multi_options = array();
		foreach ($this->block_list as $key => $value) {
			$multi_options[$key] = $value['descr'] . ' (' . $value['name'] . ')';
		}		
		
		return $multi_options;
	}
}