<?php

namespace SmartCore\Bundle\EngineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use SmartCore\Bundle\EngineBundle\Entity\SiteDomains;

/**
 * @ORM\Entity
 * @ORM\Table(name="engine_site")
 */
class Site
{
    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $site_id;
    
    /**
     * @ORM\Column(type="array")
     */
    protected $properties;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $create_datetime;
    
    /**
     * @ORM\OneToMany(targetEntity="SiteDomains", mappedBy="site_id")
     * -ORM\JoinColumn(name="site_id", referencedColumnName="site_id")
     */
    protected $site_domains;
    
    public function __construct()
    {
        $this->create_datetime = new \DateTime();
        $this->properties = new ArrayCollection();
        $this->siteDomains = new ArrayCollection();
    }
    
    public function getId()
    {
        return $this->site_id;
    }
    
    public function getSiteId()
    {
        return $this->site_id;
    }
    
    public function getProperties()
    {
        return $this->properties;
    }
    
    public function getCreateDatetime()
    {
        return $this->create_datetime;
    }
}
