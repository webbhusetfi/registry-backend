<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use AppBundle\Entity\Common\Interfaces\NameInterface;
use AppBundle\Entity\Common\Traits\NameTrait;

use AppBundle\Entity\Common\Interfaces\DescriptionInterface;
use AppBundle\Entity\Common\Traits\DescriptionTrait;

/**
 * Group
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\GroupRepository"
 * )
 */
class Group extends Entry implements NameInterface, DescriptionInterface
{
    use NameTrait;
    use DescriptionTrait;
}
