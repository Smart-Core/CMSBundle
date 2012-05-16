<?php

namespace SmartCore\Bundle\EngineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="aaa_sites_domains", indexes={@ORM\Index(name="site_id", columns={"site_id"})})
 */
class SitesDomains
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="string")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $domain;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $site_id;
	
	/**
	 * @ORM\Column(type="string", nullable=TRUE)
	 */
	protected $descr = null;
	
	/**
	 * @ORM\Column(type="string", length=8, nullable=TRUE)
	 */
	protected $language_id = null;
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $create_datetime;
}