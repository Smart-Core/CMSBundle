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
//        $blocks = $this->EM()->getRepository('SmartCoreEngineBundle:Block')->findAll();
//        ld($blocks);

        $this->blocks = array();

        $result = $this->container->get('engine.db')->query("
            SELECT *
            FROM aaa_engine_blocks
            ORDER BY position ASC");
        while ($row = $result->fetchObject()) {
            $this->blocks[$row->block_id] = array(
                'name'      => $row->name,
                'descr'     => $row->descr,
                'position'  => $row->position,
//                'owner_id'  => $row->owner_id,
//                'create_datetime' => $row->create_datetime,
            );
        }

        return $this->blocks;
    }
}
