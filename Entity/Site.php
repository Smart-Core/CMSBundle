<?php

namespace SmartCore\Bundle\EngineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use SmartCore\Bundle\EngineBundle\Entity\SiteDomains;

/**
 * @ORM\Entity
 * @ORM\Table(name="engine_sites")
 */
class Site
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @ORM\OneToMany(targetEntity="SmartCore\Bundle\EngineBundle\Entity\SiteDomains", mappedBy="site_id")
     * -ORM\JoinColumn(name="site_id", referencedColumnName="site_id")
     */
    protected $site_id;
    
    /**
     * @ORM\Column(type="string", length=8)
     */
    protected $language;

    /**
     * @ORM\Column(type="array")
     */
    protected $properties;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $create_datetime;
    
    public function __construct()
    {
        //parent::__construct();
        $this->site_id = 0;
        $this->create_datetime = new \DateTime();
        $this->language = 'ru';
        $this->properties = new ArrayCollection();
    }
    
    public function getId()
    {
        return $this->site_id;
    }
    
    public function getSiteId()
    {
        return $this->site_id;
    }
}