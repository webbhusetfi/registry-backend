<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use \JsonSerializable;

/**
 * Registry
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(name="Registry")
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\RegistryRepository"
 * )
 */
class Registry implements JsonSerializable
{
    /**
     * @var integer
     *
     * @ORM\Column(
     *      name="id",
     *      type="integer",
     *      nullable=false,
     *      options={"unsigned"=true}
     * )
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     * @Assert\Length(min = 3, max = 128)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=64, nullable=true)
     * @Assert\Length(min = 3, max = 64)
     */
    private $label;


    /**
     * Get ID
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

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
     * Set label
     *
     * @param string $label
     *
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * JSON serialize
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->label
        ];
    }
}
