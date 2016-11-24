<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Interfaces\PersonInterface;

use AppBundle\Entity\Common\Traits\PersonTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contact person
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\ContactPersonRepository"
 * )
 */
class ContactPerson extends Entry implements PersonInterface
{
    use PersonTrait;
}
