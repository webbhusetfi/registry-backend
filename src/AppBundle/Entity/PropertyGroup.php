<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PropertyGroup
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="PropertyGroup",
 *      options={"collate"="utf8_swedish_ci"},
 *      indexes={
 *          @ORM\Index(
 *              name="idx_name",
 *              columns={"name"}
 *          ),
 *          @ORM\Index(
 *              name="idx_ownerEntry_id",
 *              columns={"ownerEntry_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_registry_id",
 *              columns={"registry_id"}
 *          )
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\PropertyGroupRepository"
 * )
 */
class PropertyGroup extends Entity
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
     * @Assert\Length(
     *      min = 3,
     *      max = 64
     * )
     * @Assert\NotBlank
     */
    protected $name;

    /**
     * @var Entry
     *
     * @ORM\ManyToOne(
     *      targetEntity="Entry"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="ownerEntry_id",
     *          referencedColumnName="id",
     *          nullable=true,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    protected $ownerEntry;

    /**
     * @var Registry
     *
     * @ORM\ManyToOne(
     *      targetEntity="Registry"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="registry_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    protected $registry;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="Property",
     *      mappedBy="propertyGroup"
     * )
     */
    protected $properties;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->properties = new ArrayCollection();
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
     * Set owner entry
     *
     * @param Entry $ownerEntry
     *
     * @return self
     */
    public function setOwnerEntry(Entry $ownerEntry = null)
    {
        $this->ownerEntry = $ownerEntry;

        return $this;
    }

    /**
     * Get owner entry
     *
     * @return Entry
     */
    public function getOwnerEntry()
    {
        return $this->ownerEntry;
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
     * Get properties
     *
     * @return ArrayCollection
     */
    public function getProperties()
    {
        return $this->properties;
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
            'ownerEntry' => ($this->ownerEntry ? $this->ownerEntry->getId() : null),
            'registry' => ($this->registry ? $this->registry->getId() : null)
        ];
    }
}

