<?php
namespace SmartCore\Bundle\EngineBundle\Engine;

use SmartCore\Bundle\EngineBundle\Controller\Controller;

class Block extends Controller
{
    protected $blocks = array();
    
    /**
     * Получить список всех блоков.
     * 
     * @param int $site_id
     * @return array
     */
    public function all($site_id = false)
    {
        if (!$site_id) {
            $site_id = $this->engine('site')->getId();
        }

        if (isset($this->blocks[$site_id])) {
            return $this->blocks[$site_id];
        }

        $this->blocks[$site_id] = array();
        
        $sql = "SELECT * FROM {$this->DB->prefix()}engine_blocks WHERE site_id = '$site_id' ORDER BY pos ASC";
        $result = $this->DB->query($sql);
        while ($row = $result->fetchObject()) {
            $this->blocks[$site_id][$row->block_id] = array(
                'name'      => $row->name,
                'descr'     => $row->descr,
                'pos'       => $row->pos,
                'owner_id'  => $row->owner_id,
                'create_datetime'    => $row->create_datetime,
            );
        }

        return $this->blocks[$site_id];
    }

    /**
     * Получить массив для применения в Zend_Form multiOptions
     * 
     * @return array
     */
    public function getHtmlSelectOptionsArray()
    {
        $multi_options = array();
        foreach ($this->all() as $key => $value) {
            $multi_options[$key] = $value['descr'] . ' (' . $value['name'] . ')';
        }

        return $multi_options;
    }
}