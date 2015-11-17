<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use \JsonSerializable;

/**
 * Status
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Status",
 *      indexes={
 *          @ORM\Index(name="idx_registry_id", columns={"registry_id"}),
 *          @ORM\Index(name="idx_name", columns={"name"})
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\StatusRepository"
 * )
 */
class Status implements JsonSerializable
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
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     * @Assert\Length(min = 3, max = 64)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=32, nullable=true)
     * @Assert\Length(min = 3, max = 32)
     */
    private $label;

    /**
     * @var Registry
     *
     * @ORM\ManyToOne(targetEntity="Registry", fetch="EXTRA_LAZY")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="registry_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="CASCADE"
     *      )
     * })
     * @Assert\NotBlank
     */
    private $registry;


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
     * Set registry
     *
     * @param Registry $registry
     *
     * @return self
     */
    public function setRegistry(Registry $registry = null)
    {
        $this->registry = $registry;

        return $this;
    }

    /**
     * Get registry
     *
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->registry;
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
            'label' => $this->label,
            'registry' => ($this->registry ? $this->registry->getId() : null)
        ];
    }
}
