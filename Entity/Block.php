<?php

namespace SmartCore\Bundle\EngineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="engine_blocks",
 *      indexes={
 *          @ORM\Index(name="pos", columns={"pos"}),
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="block_site", columns={"block_id", "site_id"}),
 *          @ORM\UniqueConstraint(name="name_site", columns={"name", "site_id"}),
 *      }
 * )
 */
class Block
{
    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $block_id;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $site_id;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $pos;

    /**
     * @ORM\Column(type="string", length=50, nullable=FALSE)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=TRUE)
     */
    protected $descr;

    /**
     * @ORM\Column(type="integer")
     */
    protected $create_by_user_id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $create_datetime;
    
    public function __construct()
    {
        $this->create_by_user_id = 0;
        $this->create_datetime = new \DateTime();
        $this->site_id = 0;
        $this->pos = 0;
        $this->descr = null;
    }    
}