<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use \JsonSerializable;

/**
 * Connection
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Connection",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="UNIQUE",
 *              columns={
 *                  "parentEntry_id",
 *                  "childEntry_id",
 *                  "connectionType_id"
 *              }
 *          )
 *      },
 *      indexes={
 *          @ORM\Index(name="idx_parent_id", columns={"parentEntry_id"}),
 *          @ORM\Index(name="idx_child_id", columns={"childEntry_id"}),
 *          @ORM\Index(name="idx_status_id", columns={"status_id"}),
 *          @ORM\Index(name="idx_connectionType_id", columns={"connectionType_id"})
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\ConnectionRepository"
 * )
 * @UniqueEntity(
 *      fields={"connectionType", "childEntry", "parentEntry"}
 * )
 */
class Connection implements JsonSerializable
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
     * @ORM\Column(name="notes", type="string", length=255, nullable=true)
     * @Assert\Length(max = 255)
     */
    private $notes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $end;

    /**
     * @var string
     *
     * @ORM\Column(name="startNotes", type="string", length=255, nullable=true)
     * @Assert\Length(max = 255)
     */
    private $startNotes;

    /**
     * @var string
     *
     * @ORM\Column(name="endNotes", type="string", length=255, nullable=true)
     * @Assert\Length(max = 255)
     */
    private $endNotes;

    /**
     * @var Status
     *
     * @ORM\ManyToOne(targetEntity="Status")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="status_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="RESTRICT"
     *      )
     * })
     * @Assert\NotBlank
     */
    private $status;

    /**
     * @var ConnectionType
     *
     * @ORM\ManyToOne(targetEntity="ConnectionType")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="connectionType_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    private $connectionType;

    /**
     * @var Entry
     *
     * @ORM\ManyToOne(targetEntity="Entry")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="childEntry_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    private $childEntry;

    /**
     * @var Entry
     *
     * @ORM\ManyToOne(targetEntity="Entry")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="parentEntry_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    private $parentEntry;

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
    private $properties;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->properties = new ArrayCollection();
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
     * Set start
     *
     * @param \DateTime $start
     *
     * @return self
     */
    public function setStart(\DateTime $start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     *
     * @return self
     */
    public function setEnd(\DateTime $end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set start notes
     *
     * @param string $startNotes
     *
     * @return self
     */
    public function setStartNotes($startNotes)
    {
        $this->startNotes = $startNotes;

        return $this;
    }

    /**
     * Get start notes
     *
     * @return string
     */
    public function getStartNotes()
    {
        return $this->startNotes;
    }

    /**
     * Set end notes
     *
     * @param string $endNotes
     *
     * @return self
     */
    public function setEndNotes($endNotes)
    {
        $this->endNotes = $endNotes;

        return $this;
    }

    /**
     * Get end notes
     *
     * @return string
     */
    public function getEndNotes()
    {
        return $this->endNotes;
    }

    /**
     * Set status
     *
     * @param Status $status
     *
     * @return self
     */
    public function setStatus(Status $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
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
            'start' => (
                $this->start
                ? $this->start->format(\DateTime::ISO8601)
                : null
            ),
            'end' => (
                $this->end
                ? $this->end->format(\DateTime::ISO8601)
                : null
            ),
            'startNotes' => $this->startNotes,
            'endNotes' => $this->endNotes,
            'status' => (
                $this->status
                ? $this->status->getId()
                : null
            ),
            'connectionType' => (
                $this->connectionType
                ? $this->connectionType->getId()
                : null
            ),
            'childEntry' => (
                $this->childEntry
                ? $this->childEntry->getId()
                : null
            ),
            'parentEntry' => (
                $this->parentEntry
                ? $this->parentEntry->getId()
                : null
            ),
        ];
    }
}
