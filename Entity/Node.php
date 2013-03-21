<?php

namespace SmartCore\Bundle\EngineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="engine_nodes",
 *      indexes={
 *          @ORM\Index(name="is_active", columns={"is_active"}),
 *          @ORM\Index(name="position",  columns={"position"}),
 *          @ORM\Index(name="block_id",  columns={"block_id"}),
 *          @ORM\Index(name="module",    columns={"module"})
 *      }
 * )
 */
class Node
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $node_id;

    /**
     * @ORM\Column(type="boolean", nullable=TRUE)
     */
    protected $is_active;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     */
    protected $module;

    /**
     * @ORM\Column(type="array", nullable=TRUE)
     */
    protected $params;

    /**
     * @ORM\ManyToOne(targetEntity="Folder")
     * @ORM\JoinColumn(name="folder_id", referencedColumnName="folder_id")
     * @Assert\NotBlank()
     */
    protected $folder;

    /**
     * @ORM\ManyToOne(targetEntity="Block")
     * @ORM\JoinColumn(name="block_id", referencedColumnName="block_id")
     * @Assert\NotBlank()
     */
    protected $block;

    /**
     * Позиция в внутри блока.
     *
     * @ORM\Column(type="smallint")
     */
    protected $position;

    /**
     * Приоритет порядка выполнения.
     *
     * @ORM\Column(type="smallint")
     */
    protected $priority;

    /**
     * @ORM\Column(type="boolean")
     */
    //protected $is_cached;

    /**
     * @ORM\Column(type="text", nullable=TRUE)
     */
    //protected $cache_params;

    /**
     * @ORM\Column(type="text", nullable=TRUE)
     * 
     * @todo !!! Убрать !!! это временное поле...
     */
//    protected $cache_params_yaml;

    /**
     * @todo пересмотреть.
     * 
     * @ORM\Column(type="text", nullable=TRUE)
     */
    //protected $plugins;

    /**
     * @todo пересмотреть.
     * 
     * @ORM\Column(type="text", nullable=TRUE)
     */
    //protected $permissions;

    /**
     * @ORM\Column(type="smallint")
     */
    //protected $database_id = 0;

    /**
     * @ORM\Column(type="string")
     */
    //protected $node_action_mode = 'popup';

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
        $this->is_active = true;
        $this->is_cached = true;
        $this->position = 0;
        $this->priority = 0;
    }

    public function getId()
    {
        return $this->node_id;
    }

    public function setCreateByUserId($create_by_user_id)
    {
        $this->create_by_user_id = $create_by_user_id;
    }

    public function getCreateByUserId()
    {
        return $this->create_by_user_id;
    }

    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;
    }

    public function getIsActive()
    {
        return $this->is_active;
    }

    public function setDescr($descr)
    {
        $this->descr = $descr;
    }

    public function getDescr()
    {
        return $this->descr;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setBlock($block)
    {
        $this->block = $block;
    }

    public function getBlock()
    {
        return $this->block;
    }

    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    public function getFolder()
    {
        return $this->folder;
    }

    public function setModule($module)
    {
        $this->module = $module;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }
}
