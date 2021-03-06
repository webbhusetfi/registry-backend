<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Interfaces\DescriptionInterface;
use AppBundle\Entity\Common\Interfaces\NameInterface;

use AppBundle\Entity\Common\Traits\DescriptionTrait;
use AppBundle\Entity\Common\Traits\NameTrait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="name",
     *      type="string",
     *      length=64
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 64
     * )
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="description",
     *      type="string",
     *      length=255
     * )
     * @Assert\Length(max = 255)
     */
    protected $description;
}
