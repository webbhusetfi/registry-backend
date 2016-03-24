<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use AppBundle\Entity\Common\Interfaces\PersonInterface;
use AppBundle\Entity\Common\Traits\PersonTrait;

/**
 * Member person
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\MemberPersonRepository"
 * )
 */
class MemberPerson extends Entry implements PersonInterface
{
    use PersonTrait;
}
