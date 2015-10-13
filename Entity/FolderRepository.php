<?php

namespace SmartCore\Bundle\CMSBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Smart\CoreBundle\Doctrine\RepositoryTrait;

class FolderRepository extends EntityRepository
{
    use RepositoryTrait\FindDeleted;

    /**
     * @param Folder|null $parent_folder
     *
     * @return Folder[]
     */
    public function findByParent(Folder $parent_folder = null)
    {
        return $this->findBy(['parent_folder' => $parent_folder]);
    }
}
