<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;
use \JsonSerializable;

/**
 * Entry
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Entry",
 *      options={"collate"="utf8_swedish_ci"},
 *      indexes={
 *          @ORM\Index(
 *              name="idx_externalId",
 *              columns={"externalId"}
 *          ),
 *          @ORM\Index(
 *              name="idx_createdAt",
 *              columns={"createdAt"}
 *          ),
 *          @ORM\Index(
 *              name="idx_registry_id",
 *              columns={"registry_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_type_id",
 *              columns={"type_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_createdBy_id",
 *              columns={"createdBy_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_class",
 *              columns={"class"}
 *          )
 *      }
 * )
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(
 *      name="class",
 *      type="string",
 *      columnDefinition="ENUM('ORGANIZATION','PERSON','PLACE') NOT NULL"
 * )
 * @ORM\DiscriminatorMap({
 *      "ORGANIZATION"="Organization",
 *      "PERSON"="Person",
 *      "PLACE"="Place"
 * })
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\EntryRepository"
 * )
 */
abstract class Entry extends Entity implements JsonSerializable
{
    /**
     * @var string
     *
     * @ORM\Column(
     *      name="externalId",
     *      type="string",
     *      length=255,
     *      nullable=true
     * )
     */
    protected $externalId;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="notes",
     *      type="string",
     *      length=255,
     *      nullable=true
     * )
     * @Assert\Length(
     *      max = 255
     * )
     */
    protected $notes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(
     *      name="createdAt",
     *      type="datetime",
     *      nullable=false
     * )
     * @Assert\DateTime()
     * @Assert\NotBlank()
     */
    protected $createdAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(
     *      targetEntity="User"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="createdBy_id",
     *          referencedColumnName="id",
     *          nullable=true,
     *          onDelete="SET NULL"
     *      )
     * })
     */
    protected $createdBy;

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
     * @Assert\NotBlank()
     */
    protected $registry;

    /**
     * @var Type
     *
     * @ORM\ManyToOne(
     *      targetEntity="Type"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="type_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="RESTRICT"
     *      )
     * })
     * @Assert\NotBlank()
     */
    protected $type;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *      targetEntity="Property",
     *      inversedBy="entries"
     * )
     * @ORM\JoinTable(
     *      name="EntryProperty",
     *      joinColumns={
     *          @ORM\JoinColumn(
     *              name="entry_id",
     *              referencedColumnName="id",
     *              nullable=false,
     *              onDelete="CASCADE"
     *          )
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(
     *              name="property_id",
     *              referencedColumnName="id",
     *              nullable=false,
     *              onDelete="RESTRICT"
     *          )
     *      }
     * )
     */
    protected $properties;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="Address",
     *      mappedBy="entry"
     * )
     */
    protected $addresses;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="Connection",
     *      mappedBy="parentEntry"
     * )
     */
    protected $childConnections;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="Connection",
     *      mappedBy="childEntry"
     * )
     */
    protected $parentConnections;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->properties = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        $this->childConnections = new ArrayCollection();
        $this->parentConnections = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set external ID
     *
     * @param string $externalId
     *
     * @return self
     */
    public function setExternalId($externalId = null)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * Get external ID
     *
     * @return string
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return self
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set created at
     *
     * @param \DateTime $createdAt
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get created at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set created by
     *
     * @param User $createdBy
     *
     * @return self
     */
    public function setCreatedBy(User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get created by
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
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
     * Set type
     *
     * @param Type $type
     *
     * @return self
     */
    public function setType(Type $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get addresses
     *
     * @return ArrayCollection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * Add property
     *
     * @param Property $property
     *
     * @return self
     */
    public function addProperty(Property $property)
    {
        $this->properties[] = $property;

        return $this;
    }

    /**
     * Remove property
     *
     * @param Property $property
     */
    public function removeProperty(Property $property)
    {
        $this->properties->removeElement($property);
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
     * Set properties
     *
     * @param Property[] $properties
     *
     * @return ArrayCollection
     */
    public function setProperties(array $properties)
    {
        $this->properties->clear();
        foreach ($properties as $property) {
            $this->properties[] = $property;
        }

        return $this;
    }
}
