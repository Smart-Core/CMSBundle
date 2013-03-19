<?php

namespace SmartCore\Bundle\EngineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="engine_blocks",
 *      indexes={
 *          @ORM\Index(name="position", columns={"position"}),
 *      }
 * )
 * -UniqueEntity("name")
 * @UniqueEntity(fields="name", message="Блок с таким именем уже используется")
 */
class Block
{
    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $block_id;

    /**
     * @ORM\Column(type="smallint", nullable=TRUE)
     * -ORM\Column(type="string")
     * -Assert\Type(type="integer", message="bad :(")
     * -Assert\Regex(pattern="/\d+/", match=FALSE, message="BAD!" )
     * @Assert\Range(min = "-255", minMessage = "Минимальное значение -255.", max = "255", maxMessage = "Максимальное значение 255.")
     */
    protected $position;

    /**
     * @ORM\Column(type="string", length=50, nullable=FALSE, unique=TRUE)
     * @Assert\NotBlank()
     */
    protected $name;

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
        $this->position = 0;
        $this->descr = null;
    }

    public function __toString()
    {
        $descr = $this->getDescr();

        if (empty($descr)) {
            $full_title = $this->getName();
        } else {
            $full_title = $descr . ' (' . $this->getName() . ')';
        }

        return $full_title;
    }

    public function getId()
    {
        return $this->block_id;
    }

    public function setDescr($descr)
    {
        $this->descr = $descr;
    }

    public function getDescr()
    {
        return $this->descr;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setPosition($pos)
    {
        if (empty($pos)) {
            $pos = 0;
        }

        $this->position = $pos;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setCreateByUserId($create_by_user_id)
    {
        $this->create_by_user_id = $create_by_user_id;
    }

    public function getCreateByUserId()
    {
        return $this->create_by_user_id;
    }
}
