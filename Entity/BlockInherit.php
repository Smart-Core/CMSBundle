<?php

namespace SmartCore\Bundle\EngineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="engine_blocks_inherit",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="block_folder_site", columns={"block_id", "folder_id", "site_id"}),
 *      }
 * )
 */
class BlockInherit
{
    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     */
    protected $block_id;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $site_id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $folder_id;
    
}