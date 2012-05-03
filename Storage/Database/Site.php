<?php
/**
 * Привязка доменов к сайтам.
 * 
 * @author Artem Ryzhkov <artem@smart-core.org>
 */
namespace SmartCore\Component\Engine\Storage\Database;

class Site
{
	/**
	 * Constructor.
	 */
	public function __construct($DB)
	{
		$this->DB = $DB;
	}

	/**
	 * Получить список доменов.
	 *
	 * @param int $site_id - ид сайта, по умолчанию системый.
	 * @return array
	 */
	public function getDomainsList($site_id = false)
	{
		if ($site_id === false) {
			$site_id = $this->Env->site_id;
		}
		
		$data = array();
		$sql = "SELECT * FROM {$this->DB->prefix()}engine_sites_domains WHERE site_id = '{$site_id}' ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$data[$row->domain] = array(
				'descr' 		  => $row->descr,
				'create_datetime' => $row->create_datetime,
				'language_id'	  => $row->language_id,
				);
		}
		return $data;
	}
}