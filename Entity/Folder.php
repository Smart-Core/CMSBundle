<?php

namespace SmartCore\Bundle\EngineBundle\Entity;

//use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="engine_folders",
 *      indexes={
 *          @ORM\Index(name="is_active", columns={"is_active"}),
 *          @ORM\Index(name="is_deleted", columns={"is_deleted"}),
 *          @ORM\Index(name="pos", columns={"pos"})
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="folder_pid_uri_part", columns={"folder_pid", "uri_part"}),
 *      }
 * )
 * @UniqueEntity(fields={"uri_part", "parent_folder"}, message="в каждой подпапке должен быть уникальный сегмент URI")
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
     * @ORM\ManyToOne(targetEntity="Folder")
     * @ORM\JoinColumn(name="folder_pid", referencedColumnName="folder_id")
     */
    protected $parent_folder;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @ORM\Column(type="boolean", nullable=TRUE)
     */
    protected $is_file;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $pos;
    
    /**
     * @ORM\Column(type="string", nullable=TRUE)
     */
    protected $uri_part;
    
    /**
     * @ORM\Column(type="boolean", nullable=TRUE)
     */
    protected $is_active;
    
    /**
     * @ORM\Column(type="boolean", nullable=TRUE)
     */
    protected $is_deleted;
    
    /**
     * @ORM\Column(type="string", nullable=TRUE)
     */
    protected $descr;
    
    /**
     * @ORM\Column(type="array", nullable=TRUE)
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
     * @ORM\Column(type="boolean", nullable=TRUE)
     */
    protected $has_inherit_nodes;
    
    /**
     * @ORM\Column(type="array", nullable=TRUE)
     */
    protected $permissions;

    /**
     * @ORM\Column(type="array", nullable=TRUE)
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
     * @ORM\Column(type="integer")
     */
    protected $test;

    public function setTest($test)
    {
        $this->test = $test;
    }

    public function getTest()
    {
        return $this->test;
    }

    /**
     * @ORM\Column(type="datetime")
     */
    protected $create_datetime;
    
    public function __construct()
    {
        $this->create_by_user_id = 0;
        $this->create_datetime = new \DateTime();
        $this->meta = null;
        $this->permissions = null;
        $this->lockout_nodes = null;
        $this->is_active = true;
        $this->is_deleted = false;
        $this->is_file = false;
        $this->has_inherit_nodes = false;
        $this->uri_part = '';
        $this->template = null;
        $this->redirect_to = null;
        $this->router_node_id = null;
        $this->parent_folder = null;
        $this->pos = 0;
    }

    public function __toString()
    {
        return $this->getTitle();
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setPos($pos)
    {
        $this->pos = $pos;
    }

    public function getPos()
    {
        return $this->pos;
    }

    public function setIsFile($is_file)
    {
        $this->is_file = $is_file;
    }

    public function getIsFile()
    {
        return $this->is_file;
    }

    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;
    }

    public function getIsActive()
    {
        return $this->is_active;
    }

    public function setHasInheritNodes($has_inherit_nodes)
    {
        $this->has_inherit_nodes = $has_inherit_nodes;
    }

    public function getHasInheritNodes()
    {
        return $this->has_inherit_nodes;
    }

    public function setDescr($descr)
    {
        $this->descr = $descr;
    }

    public function getDescr()
    {
        return $this->descr;
    }

    public function setUriPart($uri_part)
    {
        $this->uri_part = $uri_part;
    }

    public function getUriPart()
    {
        return $this->uri_part;
    }

    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    public function getId()
    {
        return $this->folder_id;
    }

    public function setCreateByUserId($create_by_user_id)
    {
        $this->create_by_user_id = $create_by_user_id;
    }

    public function getCreateByUserId()
    {
        return $this->create_by_user_id;
    }

    public function setParentFolder($parent_folder)
    {
        if ($this->getId() == 1) {
            $this->parent_folder = null;
        } else {
            $this->parent_folder = $parent_folder;
        }
    }

    public function getParentFolder()
    {
        return $this->parent_folder;
    }
}
