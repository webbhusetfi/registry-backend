<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use AppBundle\Entity\Common\Interfaces\NameInterface;
use AppBundle\Entity\Common\Traits\NameTrait;

use AppBundle\Entity\Common\Interfaces\DescriptionInterface;
use AppBundle\Entity\Common\Traits\DescriptionTrait;

/**
 * Place
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\PlaceRepository"
 * )
 */
class Place extends Entry implements NameInterface, DescriptionInterface
{
    use NameTrait;
    use DescriptionTrait;
}
