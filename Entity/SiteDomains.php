<?php

namespace SmartCore\Bundle\EngineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SmartCore\Bundle\EngineBundle\Entity\Site;

/**
 * @ORM\Entity
 * @ORM\Table(name="engine_sites_domains",
 *      indexes={
 *          @ORM\Index(name="site_id", columns={"site_id"})
 *      }
 * )
 */
class SiteDomains
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $domain;

    /**
     * @ORM\Column(type="integer")
     * 
     * @ORM\ManyToOne(targetEntity="SmartCore\Bundle\EngineBundle\Entity\Site", inversedBy="site_id")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="site_id", referencedColumnName="site_id")
     * })
     */
    protected $site_id;
    
    /**
     * @ORM\Column(type="text", nullable=TRUE)
     */
    protected $descr;
    
    /**
     * @ORM\Column(type="string", length=8, nullable=TRUE)
     */
    protected $language;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $create_datetime;
    
    public function __construct()
    {
        //parent::__construct();
        $this->create_datetime = new \DateTime();
        $this->descr = null;
        $this->language = null;
    }
    
}