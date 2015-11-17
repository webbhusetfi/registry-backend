<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Organization
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Organization",
 *      indexes={
 *          @ORM\Index(name="idx_name", columns={"name"})
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\OrganizationRepository"
 * )
 */
class Organization extends Entry
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
     * @Assert\Length(min = 3, max = 64)
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
            ['class' => 'ORGANIZATION', 'name' => $this->name]
        );
    }
}
