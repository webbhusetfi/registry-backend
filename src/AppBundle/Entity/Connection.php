<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Proxy;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Connection
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Connection",
 *      options={"collate"="utf8_swedish_ci"},
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="UNIQUE",
 *              columns={"parentEntry_id","childEntry_id","connectionType_id"}
 *          )
 *      },
 *      indexes={
 *          @ORM\Index(
 *              name="idx_parentEntry_id",
 *              columns={"parentEntry_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_childEntry_id",
 *              columns={"childEntry_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_connectionType_id",
 *              columns={"connectionType_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_createdBy_id",
 *              columns={"createdBy_id"}
 *          )
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\ConnectionRepository"
 * )
 * @UniqueEntity(
 *      fields={"connectionType","childEntry","parentEntry"}
 * )
 */
class Connection extends Entity
{
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
     *      nullable=true
     * )
     * @Assert\DateTime()
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
     * @var ConnectionType
     *
     * @ORM\ManyToOne(
     *      targetEntity="ConnectionType"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="connectionType_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="CASCADE"
     *      )
     * })
     * @Assert\NotBlank()
     */
    protected $connectionType;

    /**
     * @var Entry
     *
     * @ORM\ManyToOne(
     *      targetEntity="Entry",
     *      inversedBy="parentConnections"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="childEntry_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="CASCADE"
     *      )
     * })
     * @Assert\NotBlank()
     */
    protected $childEntry;

    /**
     * @var Entry
     *
     * @ORM\ManyToOne(
     *      targetEntity="Entry",
     *      inversedBy="childConnections"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="parentEntry_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="CASCADE"
     *      )
     * })
     * @Assert\NotBlank()
     */
    protected $parentEntry;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *      targetEntity="Property",
     *      inversedBy="connections"
     * )
     * @ORM\JoinTable(
     *      name="ConnectionProperty",
     *      joinColumns={
     *          @ORM\JoinColumn(
     *              name="connection_id",
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
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->properties = new ArrayCollection();
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
     * Set connection type
     *
     * @param ConnectionType $connectionType
     *
     * @return self
     */
    public function setConnectionType(ConnectionType $connectionType = null)
    {
        $this->connectionType = $connectionType;

        return $this;
    }

    /**
     * Get connection type
     *
     * @return ConnectionType
     */
    public function getConnectionType()
    {
        return $this->connectionType;
    }

    /**
     * Set child entry
     *
     * @param Entry $childEntry
     *
     * @return self
     */
    public function setChildEntry(Entry $childEntry = null)
    {
        $this->childEntry = $childEntry;

        return $this;
    }

    /**
     * Get child entry
     *
     * @return Entry
     */
    public function getChildEntry()
    {
        return $this->childEntry;
    }

    /**
     * Set parent entry
     *
     * @param Entry $parentEntry
     *
     * @return self
     */
    public function setParentEntry(Entry $parentEntry = null)
    {
        $this->parentEntry = $parentEntry;

        return $this;
    }

    /**
     * Get parent entry
     *
     * @return Entry
     */
    public function getParentEntry()
    {
        return $this->parentEntry;
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
     * JSON serialize
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'notes' => $this->notes,
            'createdAt' => (
                $this->createdAt
                ? $this->createdAt->format(\DateTime::ISO8601)
                : null
            ),
            'connectionType' => (
                $this->connectionType instanceof Proxy
                    && !$this->connectionType->__isInitialized()
                ? $this->connectionType->getId()
                : $this->connectionType->jsonSerialize()
            ),
            'childEntry' => (
                $this->childEntry instanceof Proxy
                    && !$this->childEntry->__isInitialized()
                ? $this->childEntry->getId()
                : $this->childEntry->jsonSerialize()
            ),
            'parentEntry' => (
                $this->parentEntry instanceof Proxy
                    && !$this->parentEntry->__isInitialized()
                ? $this->parentEntry->getId()
                : $this->parentEntry->jsonSerialize()
            ),
        ];
    }
}
