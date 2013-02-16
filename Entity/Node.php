<?php

namespace SmartCore\Bundle\EngineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="engine_nodes",
 *         indexes={
 *             @ORM\Index(name="is_active", columns={"is_active"}),
 *             @ORM\Index(name="position", columns={"position"}),
 *             @ORM\Index(name="block_id", columns={"block_id"}),
 *             @ORM\Index(name="module_id", columns={"module_id"})
 *         },
 *         uniqueConstraints={
 *             @ORM\UniqueConstraint(name="node_folder", columns={"node_id", "folder_id"}),
 *         }
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
     * Позиция в внутри блока.
     *
     * @ORM\Column(type="smallint")
     */
    protected $position = 0;

    /**
     * Приоритет порядка выполнения.
     *
     * @ORM\Column(type="smallint")
     */
    protected $priority = 0;

    /**
     * @ORM\Column(type="string", length=50)
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
     * 
     * @todo !!! Убрать !!! это временное поле...
     */
//    protected $cache_params_yaml;

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

    }
}
