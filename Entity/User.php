<?php

namespace SmartCore\Bundle\CMSBundle\Entity;

//use Doctrine\ORM\Mapping as ORM;
use SmartCore\Bundle\CMSBundle\Model\UserModel;

/**
 * EXAMPLE
 *
 * ORM\Entity
 * ORM\Table(name="users",
 *      indexes={
 *          ORM\Index(columns={"firstname"}),
 *          ORM\Index(columns={"lastname"})
 *      }
 * )
 */
class User extends UserModel
{
}
