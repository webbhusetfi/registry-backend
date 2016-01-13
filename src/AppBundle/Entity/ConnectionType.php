<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use \JsonSerializable;

/**
 * ConnectionType
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="ConnectionType",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="UNIQUE",
 *              columns={
 *                  "registry_id","parentType_id","childType_id","ownerEntry_id"
 *              }
 *          )
 *      },
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
 *              name="idx_parentType_id",
 *              columns={"parentType_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_childType_id",
 *              columns={"childType_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_registry_id",
 *              columns={"registry_id"}
 *          )
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\ConnectionTypeRepository"
 * )
 * @UniqueEntity(
 *      fields={"registry","parentType","childType","ownerEntry"}
 * )
 */
class ConnectionType implements JsonSerializable
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
     * @ORM\Id()
     * @ORM\GeneratedValue(
     *      strategy="IDENTITY"
     * )
     */
    private $id;

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
    private $name;

    /**
     * @var Type
     *
     * @ORM\ManyToOne(
     *      targetEntity="Type"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="childType_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="RESTRICT")
     * })
     */
    private $childType;

    /**
     * @var Type
     *
     * @ORM\ManyToOne(
     *      targetEntity="Type"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="parentType_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="RESTRICT"
     *      )
     * })
     */
    private $parentType;

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
    private $ownerEntry;

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
    private $registry;



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
     * Set child type
     *
     * @param Type $childType
     *
     * @return self
     */
    public function setChildType(Type $childType = null)
    {
        $this->childType = $childType;

        return $this;
    }

    /**
     * Get child type
     *
     * @return Type
     */
    public function getChildType()
    {
        return $this->childType;
    }

    /**
     * Set parent type
     *
     * @param Type $parentType
     *
     * @return self
     */
    public function setParentType(Type $parentType = null)
    {
        $this->parentType = $parentType;

        return $this;
    }

    /**
     * Get parent type
     *
     * @return Type
     */
    public function getParentType()
    {
        return $this->parentType;
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
     * JSON serialize
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->label,
            'childType' => (
                $this->childType
                ? $this->childType->getId()
                : null
            ),
            'parentType' => (
                $this->parentType
                ? $this->parentType->getId()
                : null
            ),
            'ownerEntry' => (
                $this->ownerEntry
                ? $this->ownerEntry->getId()
                : null
            ),
        ];
    }
}
