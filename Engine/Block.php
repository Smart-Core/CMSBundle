<?php
namespace SmartCore\Bundle\EngineBundle\Engine;

use Symfony\Component\DependencyInjection\ContainerAware;

class Block extends ContainerAware
{
    protected $blocks = array();
    
    /**
     * Получить список всех блоков.
     * 
     * @return array
     */
    public function all()
    {
        $this->blocks = array();

        $result = $this->container->get('engine.db')->query("SELECT * FROM engine_blocks ORDER BY pos ASC");
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
    public function __getHtmlSelectOptionsArray()
    {
        $multi_options = array();
        foreach ($this->all() as $key => $value) {
            $multi_options[$key] = $value['descr'] . ' (' . $value['name'] . ')';
        }

        return $multi_options;
    }
}
