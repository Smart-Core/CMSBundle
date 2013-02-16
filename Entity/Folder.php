<?php

namespace SmartCore\Bundle\EngineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="engine_folders",
 *      indexes={
 *          @ORM\Index(name="is_active", columns={"is_active"}),
 *          @ORM\Index(name="is_deleted", columns={"is_deleted"}),
 *          @ORM\Index(name="pos", columns={"pos"})
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="pid_uri_part", columns={"pid", "uri_part"}),
 *      }
 * )
 */
class Folder
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $folder_id;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $pid;
    
    /**
     * @ORM\Column(type="smallint")
     */
    protected $pos;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $uri_part;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_active;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_deleted;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_file;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $title;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $descr;
    
    /**
     * @ORM\Column(type="array")
     */
    protected $meta;

    /**
     * @ORM\Column(type="string", nullable=TRUE)
     */
    protected $redirect_to;
    
    /**
     * @ORM\Column(type="integer", nullable=TRUE)
     */
    protected $router_node_id;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $has_inherit_nodes = 0;
    
    /**
     * @ORM\Column(type="array")
     */
    protected $permissions;

    /**
     * @ORM\Column(type="array")
     */
    protected $lockout_nodes;

    /**
     * @ORM\Column(type="string", length=30, nullable=TRUE)
     */
    protected $template;

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
        $this->meta = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->lockout_nodes = new ArrayCollection();
        $this->is_active = 1;
        $this->is_deleted = 0;
        $this->is_file = 0;
        $this->has_inherit_nodes = 0;
        $this->uri_part = '';
        $this->template = null;
        $this->redirect_to = null;
        $this->router_node_id = null;
        $this->pid = 0;
        $this->pos = 0;
    }
}
