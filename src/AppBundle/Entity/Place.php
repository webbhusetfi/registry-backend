<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Place
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Place",
 *      indexes={
 *          @ORM\Index(name="idx_name", columns={"name"})
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\PlaceRepository"
 * )
 */
class Place extends Entry
{
    /**
     * @var string
     *
     * @ORM\Column(
     *      name="name",
     *      type="string",
     *      length=64,
     *      nullable=false
     * )
     * @Assert\Length(min = 1, max = 64)
     * @Assert\NotBlank
     */
    private $name;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * JSON serialize
     *
     * @return array
     */
    public function jsonSerialize() {
        return array_merge(
            parent::jsonSerialize(),
            ['name' => $this->name]
        );
    }
}
