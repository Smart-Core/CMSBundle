<?php
/**
 * Привязка доменов к сайтам.
 * 
 * @author Artem Ryzhkov <artem@smart-core.org>
 */
namespace SmartCore\Bundle\EngineBundle\Storage\Database;

class Site
{
    // @todo сделать кеши... притом надо сделать через мемкеш и АРС.
    protected $cache_properties = null;
    protected $cache_domains = null;

    /**
     * Constructor.
     */
     public function __construct($DB)
    {
        $this->DB = $DB;
        //$this->DB->exec("'SET TIME_ZONE = '" . date_default_timezone_get() . "'");        
    }

    /**
     * Получить список доменов.
     *
     * @param int $site_id - ид сайта.
     * @return array
     */
    public function getDomainsList($site_id)
    {
        $data = array();
        $sql = "SELECT * FROM {$this->DB->prefix()}engine_sites_domains WHERE site_id = '{$site_id}' ";
        $result = $this->DB->query($sql);
        while ($row = $result->fetchObject()) {
            $data[$row->domain] = array(
                'descr'           => $row->descr,
                'create_datetime' => $row->create_datetime,
                'language_id'     => $row->language_id,
            );
        }

        return $data;
    }
    
    /**
     * Получить свойства сайта.
     *
     * @param int $site_id - ID сайта, по умолчанию системый.
     * @return array
     */
    public function getProperties($site_id)
    {
        $sql = "SELECT * FROM {$this->DB->prefix()}engine_sites WHERE site_id = '{$site_id}' ";

        if ($row = $this->DB->fetchObject($sql)) {
            $properties = unserialize($row->properties);
            $properties['create_datetime'] = $row->create_datetime;
            $properties['domains'] = $this->getDomainsList($site_id);
            return $properties;
        } else {
            return null;
        }
    }
}