<?php

namespace SmartCore\Bundle\EngineBundle\Entity;

use Doctrine\ORM\EntityRepository;

class FolderRepository extends EntityRepository
{
    public function findByParent(Folder $parent_folder = null)
    {
        return $this->findBy(array('parent_folder' => $parent_folder));
    }

    public function getTree()
    {
        /*
        return $this->getEntityManager()
            ->createQuery('SELECT p FROM AcmeStoreBundle:Product p ORDER BY p.name ASC')
            ->getResult();
        */
    }
}
