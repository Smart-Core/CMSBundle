<?php

namespace SmartCore\Bundle\EngineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="aaa_sites")
 */
class Sites
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $site_id;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $create_datetime;
    
    /**
     * @ORM\Column(type="text", nullable=TRUE)
     */
    protected $properties = null;
}