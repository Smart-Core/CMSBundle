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
    public function all()
    {
        $this->blocks = array();
        
        $sql = "SELECT * FROM {$this->DB->prefix()}engine_blocks ORDER BY pos ASC";
        $result = $this->DB->query($sql);
        while ($row = $result->fetchObject()) {
            $this->blocks[$row->block_id] = array(
                'name'      => $row->name,
                'descr'     => $row->descr,
                'pos'       => $row->pos,
                'owner_id'  => $row->owner_id,
                'create_datetime' => $row->create_datetime,
            );
        }

        return $this->blocks;
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