<?php

namespace SmartCore\Bundle\EngineBundle\Entity;

use Doctrine\ORM\EntityRepository;

class NodeRepository extends EntityRepository
{
    public function findIn(array $list)
    {
        $list_string = '';
        foreach ($list as $node_id) {
            $list_string .= $node_id . ',';
        }

        $list_string = substr($list_string, 0, strlen($list_string)-1);

        return $this->_em->createQuery("
            SELECT n
            FROM {$this->_entityName} n
            WHERE n.node_id IN({$list_string})
            ORDER BY n.position ASC
        ")->getResult();
    }
}
