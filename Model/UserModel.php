<?php

namespace SmartCore\Bundle\CMSBundle\Model;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * ORM\Entity
 * ORM\Table(name="users")
 */
class UserModel extends BaseUser
{
    use ColumnTrait\CreatedAt;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $firstname;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $lastname;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->created_at = new \DateTime();
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * Get the full name of the user (first + last name).
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->getFirstName().' '.$this->getLastname();
    }
}
