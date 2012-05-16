<?php

namespace SmartCore\Bundle\EngineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="aaa_sites_nodes",
 * 		indexes={
 * 			@ORM\Index(name="is_active", columns={"is_active"}),
 * 			@ORM\Index(name="pos", columns={"pos"})
 * 		},
 * 		uniqueConstraints={
 * 			@ORM\UniqueConstraint(name="_PRIMARY", columns={"node_id", "folder_id", "site_id"}),
 * 		}
 * )
 * 
 */
class Nodes
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $node_id;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $site_id = 0;
	
	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $is_active = 0;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $folder_id;
	
	/**
	 * @ORM\Column(type="smallint")
	 */
	protected $block_id = 0;
	
	/**
	 * @ORM\Column(type="smallint")
	 */
	protected $pos = 0;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $module_id;
	
	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $is_cached = 1;
	
	/**
	 * @ORM\Column(type="text", nullable=TRUE)
	 */
	protected $cache_params;
	
	/**
	 * @ORM\Column(type="text", nullable=TRUE)
	 */
	protected $cache_params_yaml;
	
	/**
	 * @ORM\Column(type="text", nullable=TRUE)
	 */
	protected $params;
	
	/**
	 * @todo пересмотреть.
	 * 
	 * @ORM\Column(type="text", nullable=TRUE)
	 */
	protected $plugins;
	
	/**
	 * @todo пересмотреть.
	 * 
	 * @ORM\Column(type="text", nullable=TRUE)
	 */
	protected $permissions;
	
	/**
	 * @ORM\Column(type="integer", nullable=TRUE)
	 */
	protected $owner_id;
	
	/**
	 * @ORM\Column(type="smallint")
	 */
	protected $database_id = 0;
	
	/**
	 * @ORM\Column(type="string")
	 */
	protected $node_action_mode = 'popup';
	
	/**
	 * @ORM\Column(type="string", nullable=TRUE)
	 */
	protected $descr = null;
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $create_datetime;
}