<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Property
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Property",
 *      options={"collate"="utf8_swedish_ci"},
 *      indexes={
 *          @ORM\Index(
 *              name="idx_name",
 *              columns={"name"}
 *          ),
 *          @ORM\Index(
 *              name="idx_propertyGroup_id",
 *              columns={"propertyGroup_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_connectionType_id",
 *              columns={"connectionType_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_ownerEntry_id",
 *              columns={"ownerEntry_id"}
 *          )
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\PropertyRepository"
 * )
 */
class Property extends Entity
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
     *      min = 1,
     *      max = 64
     * )
     * @Assert\NotBlank()
     */
    protected $name;

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
     *      min = 1,
     *      max = 255
     * )
     */
    protected $notes;

    /**
     * @var PropertyGroup
     *
     * @ORM\ManyToOne(
     *      targetEntity="PropertyGroup",
     *      inversedBy="properties"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="propertyGroup_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    protected $propertyGroup;

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
     *          nullable=true,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    protected $connectionType;

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
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *      targetEntity="Connection",
     *      mappedBy="properties"
     * )
     */
    protected $connections;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *      targetEntity="Entry",
     *      mappedBy="properties"
     * )
     */
    protected $entries;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->connections = new ArrayCollection();
        $this->entries = new ArrayCollection();
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
     * Set property group
     *
     * @param PropertyGroup $propertyGroup
     *
     * @return self
     */
    public function setPropertyGroup(PropertyGroup $propertyGroup = null)
    {
        $this->propertyGroup = $propertyGroup;

        return $this;
    }

    /**
     * Get property group
     *
     * @return PropertyGroup
     */
    public function getPropertyGroup()
    {
        return $this->propertyGroup;
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
     * Add connection
     *
     * @param Connection $connection
     *
     * @return self
     */
    public function addConnection(Connection $connection)
    {
        $this->connections[] = $connection;

        return $this;
    }

    /**
     * Remove connection
     *
     * @param Connection $connection
     */
    public function removeConnection(Connection $connection)
    {
        $this->connections->removeElement($connection);
    }

    /**
     * Get connections
     *
     * @return ArrayCollection
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Add entry
     *
     * @param Entry $entry
     *
     * @return self
     */
    public function addEntry(Entry $entry)
    {
        $this->entries[] = $entry;

        return $this;
    }

    /**
     * Remove entry
     *
     * @param Entry $entry
     */
    public function removeEntry(Entry $entry)
    {
        $this->entries->removeElement($entry);
    }

    /**
     * Get entries
     *
     * @return ArrayCollection
     */
    public function getEntries()
    {
        return $this->entries;
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
            'notes' => $this->notes,
            'propertyGroup' => ($this->propertyGroup ? $this->propertyGroup->getId() : null),
            'connectionType' => ($this->connectionType ? $this->connectionType->getId() : null),
            'ownerEntry' => ($this->ownerEntry ? $this->ownerEntry->getId() : null)
        ];
    }
}
