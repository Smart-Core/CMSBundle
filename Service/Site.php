<?php
namespace SmartCore\Bundle\EngineBundle\Service;

use SmartCore\Bundle\EngineBundle\Controller\Controller;

class Site extends Controller
{
    protected $id;
    protected $create_datetime;
    protected $properties;
    protected $domains_list;
    
    protected $booted;

    // @todo подумать, может быть убрать http_theme в другой сервис, например env или theme.
    protected $http_theme       = ''; // ??? HTTP путь к теме оформления.

    /**
     * Хак: игнорирование конструктора котроллера, в котором инициализируется View.
     */
    public function __construct()
    {
        $this->id = false;
        $this->create_datetime = null;
        $this->properties = array();
        $this->domains_list = array();
        
        $this->booted = false;
    }

    /**
     * Инициализация сайта.
     *
     * @return bool
     */
    public function init()
    {
        if (true === $this->booted) {
            return;
        }        
        
        $dir_sites = $this->engine('env')->get('dir_sites');
        
        if (empty($dir_sites)) {
            // в односайтовом режиме включается сайт по умолчанию, по принципу, самый младший site_id в БД.
            $site = $this->getRepo('SmartCoreEngineBundle:Site')->findBy(array(), array('site_id' => 'ASC'), 1);
        } else {
            $site = $this->DQL("SELECT s 
                FROM SmartCoreEngineBundle:Site s 
                JOIN s.site_domains d 
                WHERE d.domain = '" . $this->engine('env')->get('http_host') . "'")
            ->getResult();
        }
        
        if (isset($site[0])) {
            $site_id = $site[0]->getId();
            $this->id = $site[0]->getId();
            $this->create_datetime = $site[0]->getCreateDatetime();
            $this->properties = $site[0]->getProperties();
        } else {
            return false;
        }
                
        if (!empty($this->properties['session_name'])) {
            session_name($this->properties['session_name']);
        }

        date_default_timezone_set($this->properties['timezone']);

        // Если указан dir_sites, то считается, что применяется мультисайтовый режим.
        if (strlen($dir_sites) == 0) {
            $this->http_theme = $this->engine('env')->get('base_path') . $this->properties['dir_themes'];
        } else {
            $this->http_theme = $this->engine('env')->get('base_path') . $dir_sites . $site_id . '/' . $$this->properties['dir_themes'];
        }
                
        $this->booted = true;
        return true;
    }

    /**
     * Получить все свойства сайта.
     *
     * @return array
     */
    public function getProperties()
    {
        $this->init();
        
        return $this->properties;
    }

    /**
     * Получить все свойства сайта.
     *
     * @return string
     */
    public function getProperty($name)
    {
        $this->init();
        
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        } else {
            throw new \RuntimeException('Site property "%s" not supported.', $name);
        }
    }

    /**
     * Получить список доменов.
     *
     * @return array
     */
    public function getDomainsList()
    {
        $this->init();

        if (empty($this->domains_list)) {
            $this->domains_list = $this->DQL("SELECT d FROM SmartCoreEngineBundle:SiteDomains d WHERE d.site_id = '{$this->id}'")->getResult();
        }
        
        return $this->domains_list;
    }

    /**
     * Получить дату создания.
     * 
     * @return \DateTime object
     */
    public function getCreateDatetime()
    {
        $this->init();

        return $this->create_datetime;
    }
    
    /**
     * Получить ID сайта.
     * 
     * @return int
     */
    public function getId()
    {
        $this->init();

        return $this->id;
    }

    /**
     * Получить robots.txt
     * 
     * @return string
     */
    public function getRobotsTxt()
    {
        return $this->getProperty('robots_txt');
    }

    /**
     * Получить префикс куки.
     * 
     * @return string
     */
    public function getCookiePrefix()
    {
        return $this->getProperty('cookie_prefix');
    }

    /**
     * Получить флаг, является ли сайт мультиязычным.
     * 
     * @return bool
     */
    public function isMultiLanguage()
    {
        return $this->getProperty('is_multi_language');
    }
}
