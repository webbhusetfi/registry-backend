<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Registry
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Registry",
 *      options={"collate"="utf8_swedish_ci"},
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="UNIQUE",
 *              columns={"name"}
 *          )
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\RegistryRepository"
 * )
 */
class Registry extends Entity
{
    /**
     * @var string
     *
     * @ORM\Column(
     *      name="name",
     *      type="string",
     *      length=128,
     *      nullable=false
     * )
     * @Assert\Length(
     *      min = 3,
     *      max = 128
     * )
     * @Assert\NotBlank()
     */
    protected $name;

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
}
