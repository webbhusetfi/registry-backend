<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;

use AppBundle\Entity\Common\Interfaces\ClassNameInterface;
use AppBundle\Entity\Common\Traits\ClassNameTrait;

/**
 * Entry
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Entry",
 *      options={"collate"="utf8_swedish_ci"},
 *      indexes={
 *          @ORM\Index(
 *              name="idx_registry_id",
 *              columns={"registry_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_createdBy_id",
 *              columns={"createdBy_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_createdAt",
 *              columns={"createdAt"}
 *          ),
 *          @ORM\Index(
 *              name="idx_type",
 *              columns={"type"}
 *          ),
 *          @ORM\Index(
 *              name="idx_externalId",
 *              columns={"externalId"}
 *          ),
 *          @ORM\Index(
 *              name="idx_name",
 *              columns={"name"}
 *          ),
 *          @ORM\Index(
 *              name="idx_gender",
 *              columns={"gender"}
 *          ),
 *          @ORM\Index(
 *              name="idx_firstName",
 *              columns={"firstName"}
 *          ),
 *          @ORM\Index(
 *              name="idx_lastName",
 *              columns={"lastName"}
 *          ),
 *          @ORM\Index(
 *              name="idx_birthYear",
 *              columns={"birthYear"}
 *          ),
 *          @ORM\Index(
 *              name="idx_birthMonth",
 *              columns={"birthMonth"}
 *          ),
 *          @ORM\Index(
 *              name="idx_birthDay",
 *              columns={"birthDay"}
 *          )
 *      }
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(
 *      name="type",
 *      type="string",
 *      columnDefinition="ENUM('UNION','ASSOCIATION','GROUP','PLACE','MEMBER_PERSON','MEMBER_ORGANIZATION','CONTACT_PERSON','CONTACT_ORGANIZATION') NOT NULL"
 * )
 * @ORM\DiscriminatorMap({
 *      "UNION"="Union",
 *      "ASSOCIATION"="Association",
 *      "GROUP"="Group",
 *      "PLACE"="Place",
 *      "MEMBER_PERSON"="MemberPerson",
 *      "MEMBER_ORGANIZATION"="MemberOrganization",
 *      "CONTACT_PERSON"="ContactPerson",
 *      "CONTACT_ORGANIZATION"="ContactOrganization"
 * })
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\EntryRepository"
 * )
 */
abstract class Entry extends Entity implements ClassNameInterface
{
    use ClassNameTrait;

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
     *              onDelete="CASCADE"
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
     *      targetEntity="EntryInvoice",
     *      mappedBy="entry"
     * )
     */
    protected $entryInvoices;

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
        $this->entryInvoices = new ArrayCollection();
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
     * Get addresses
     *
     * @return ArrayCollection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * Get entry invoices
     *
     * @return ArrayCollection
     */
    public function getEntryInvoices()
    {
        return $this->entryInvoices;
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
