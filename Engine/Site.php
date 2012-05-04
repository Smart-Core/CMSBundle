<?php
namespace SmartCore\Bundle\EngineBundle\Engine;

use SmartCore\Bundle\EngineBundle\Controller\Controller;

class Site extends Controller
{
	protected $storage;
	
	protected $id					= false;
	protected $create_datetime		= null;
	protected $short_name	 		= '';
	protected $full_name	 		= '';
	protected $language_id	 		= 'ru';
	protected $http_lang_prefix		= '';
	protected $is_multi_language	= false;
	protected $is_cache_enable		= false;
	protected $timezone	 			= 'UTC';
	protected $cookie_prefix		= '';
	protected $session_name			= false;
	protected $http_theme			= '';		// ??? HTTP путь к теме оформления (ex. dir_theme) складывается из Env->dir_sites + Env->theme_path + Site->theme_path.
	protected $defult_theme			= 'default';
	protected $themes				= array('default' => '');
	protected $layouts				= array();
	protected $views				= array();	// ??? подумать, может быть можно вообще без старого механизама 'views', а заюзать например расширение макетов, как в Twig.
	protected $robots_txt			= '';
	protected $root_layout			= '';
	protected $root_view			= '';
		
	/**
	 * Constructor.
	 *
	public function __construct()
	{
//		parent::__construct();
		
//		cmf_dump($DB);
//		cmf_dump(123);
		
		//$this->storage = new Storage\Database\Site($DB);
	}
	*/
	
	/**
	 * Инициализация сайта.
	 *
	 * Используется, как статический метод для того, чтобы можно было использовать в Cron для обращения к нодам.
	 *  
	 * @param int $site_id - инициализировать заданный site_id
	 * @param string $domain - инициализировать заданный domain @todo 
	 * @return bool
	 * 
	 * @todo обработку входящего $domain.
	 */
	public function init($site_id = false, $domain = false)
	{
		// @todo пока так включается сайт по умолчанию, по принципу, самый младший site_id в БД.
		$dir_sites = $this->Env->get('dir_sites');
		if (empty($dir_sites)) {
			$sql = "SELECT site_id FROM {$this->DB->prefix()}engine_sites ORDER BY site_id ASC LIMIT 1";
			$site_id = $this->DB->fetchObject($sql)->site_id;
		}
		
		if ($site_id === false) {
			$sql = "SELECT site.*, domain.language_id AS domain_language_id, theme.path AS theme_path, theme.content_language, theme.doctype, theme.theme_id
				FROM 
					{$this->DB->prefix()}engine_sites AS site, 
					{$this->DB->prefix()}engine_themes AS theme, 
					{$this->DB->prefix()}engine_sites_domains AS domain
				WHERE domain.domain = '" . HTTP_HOST . "' AND site.theme_id = theme.theme_id AND site.site_id = theme.site_id AND site.site_id = domain.site_id ";
		} else {
			// @todo сейчас если указан $site_id, то не учитывается язык домена. 
			/*
			$sql = "SELECT site.*, theme.path AS theme_path, theme.content_language, theme.doctype, theme.theme_id
				FROM engine_sites AS site, engine_themes AS theme
				WHERE site.site_id = '$site_id' AND site.theme_id = theme.theme_id AND site.site_id = theme.site_id ";
			*/
			$sql = "SELECT * FROM {$this->DB->prefix()}engine_sites WHERE site_id = '$site_id'";
		}
		
		$row = $this->DB->fetchObject($sql);
		if (empty($row)) {
			return false;
		}
		
		$properties = unserialize($row->properties);
		
		foreach ($this as $key => $_dummy) {
			if (isset($properties[$key])) {
				$this->$key = $properties[$key];
			}
		}
		
		if (!empty($this->session_name)) {
			session_name($this->session_name);
		}

		date_default_timezone_set($this->timezone);
		//$this->DB->exec("'SET TIME_ZONE = '" . date_default_timezone_get() . "'");

		$this->id = $row->site_id;
		$this->create_datetime = $row->create_datetime;
		
		// Если указан dir_sites, то считается, что применяется мультисайтовый режим.
		if (strlen($this->Env->get('dir_sites')) == 0) {
			$this->http_theme = $this->Env->base_path . $properties['dir_themes'];
		} else {
			$this->http_theme = $this->Env->base_path . $this->Env->get('dir_sites') . $row->site_id . '/' . $properties['dir_themes'];
		}
		
		return true;
	}
	
	/**
	 * Получить свойства сайта.
	 *
	 * @param int $site_id - ID сайта, по умолчанию системый.
	 * @return array
	 */
	public function getProperties($site_id = false)
	{		
		if ($this->id === false) {
			if ($site_id === false) {
				/*
				$data = array();
				foreach ($this as $key => $value) {
					if ($key == 'container' or $key == 'View') {
						continue;
					}
					$data[$key] = $value;
				}
				return $data;
				*/
				return null;
			} else {
				$this->init($site_id);
			}
		}
		
		$sql = "SELECT * FROM {$this->DB->prefix()}engine_sites WHERE site_id = '{$site_id}' ";
		
		if ($row = $this->DB->fetchObject($sql)) {
			$properties = unserialize($row->properties);
			$properties['create_datetime'] = $row->create_datetime;
			return $properties;
		} else {
			return null;
		}	
	}	

	/**
	 * Получить список доменов.
	 *
	 * @param int $site_id - ид сайта, по умолчанию системый.
	 * @return array
	 */
	public function getDomainsList($site_id = false)
	{
		return $this->storage->getDomainsList($site_id);
	}
	
	/**
	 * Получить ID сайта.
	 * 
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Получить robots.txt
	 * 
	 * @return string
	 */
	public function getRobotsTxt()
	{
		return $this->robots_txt;
	}
	
	/**
	 * Получить префикс куки.
	 * 
	 * @return string
	 */
	public function getCookiePrefix()
	{
		return $this->cookie_prefix;
	}
	
	/**
	 * Получить HTTP_LANG_PREFIX.
	 * 
	 * @return string
	 * 
	 * @todo пересмотреть и переименовать во что-то типа lang_prefix_uri или просто lang_prefix.
	 */
	public function getHttpLangPrefix()
	{
		return $this->http_lang_prefix;
	}

	/**
	 * Получить флаг, является ли сайт мультиязычным.
	 * 
	 * @return bool
	 */
	public function isMultiLanguage()
	{
		return $this->is_multi_language;
	}
}