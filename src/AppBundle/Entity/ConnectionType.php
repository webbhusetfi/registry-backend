<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ConnectionType
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="ConnectionType",
 *      options={"collate"="utf8_swedish_ci"},
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="UNIQUE",
 *              columns={
 *                  "registry_id","parentType","childType","ownerEntry_id"
 *              }
 *          )
 *      },
 *      indexes={
 *          @ORM\Index(
 *              name="idx_registry_id",
 *              columns={"registry_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_parentType",
 *              columns={"parentType"}
 *          ),
 *          @ORM\Index(
 *              name="idx_childType",
 *              columns={"childType"}
 *          ),
 *          @ORM\Index(
 *              name="idx_ownerEntry_id",
 *              columns={"ownerEntry_id"}
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
class ConnectionType extends Entity
{
    const TYPE_UNION = 'UNION';
    const TYPE_ASSOCIATION = 'ASSOCIATION';
    const TYPE_GROUP = 'GROUP';
    const TYPE_PLACE = 'PLACE';
    const TYPE_MEMBER_PERSON = 'MEMBER_PERSON';
    const TYPE_MEMBER_ORGANIZATION = 'MEMBER_ORGANIZATION';
    const TYPE_CONTACT_PERSON = 'CONTACT_PERSON';
    const TYPE_CONTACT_ORGANIZATION = 'CONTACT_ORGANIZATION';

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="parentType",
     *      type="string",
     *      nullable=false,
     *      columnDefinition="ENUM('UNION','ASSOCIATION','GROUP','PLACE','MEMBER_PERSON','MEMBER_ORGANIZATION','CONTACT_PERSON','CONTACT_ORGANIZATION') NOT NULL"
     * )
     * @Assert\Choice(
     *      choices={
     *          "UNION",
     *          "ASSOCIATION",
     *          "GROUP",
     *          "PLACE",
     *          "MEMBER_PERSON",
     *          "MEMBER_ORGANIZATION",
     *          "CONTACT_PERSON",
     *          "CONTACT_ORGANIZATION",
     *      }
     * )
     * @Assert\NotBlank()
     */
    protected $parentType;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="childType",
     *      type="string",
     *      nullable=false,
     *      columnDefinition="ENUM('UNION','ASSOCIATION','GROUP','PLACE','MEMBER_PERSON','MEMBER_ORGANIZATION','CONTACT_PERSON','CONTACT_ORGANIZATION') NOT NULL"
     * )
     * @Assert\Choice(
     *      choices={
     *          "UNION",
     *          "ASSOCIATION",
     *          "GROUP",
     *          "PLACE",
     *          "MEMBER_PERSON",
     *          "MEMBER_ORGANIZATION",
     *          "CONTACT_PERSON",
     *          "CONTACT_ORGANIZATION",
     *      }
     * )
     * @Assert\NotBlank()
     */
    protected $childType;

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
     * Set parent type
     *
     * @param string $parentType
     *
     * @return self
     */
    public function setParentType($parentType = null)
    {
        $this->parentType = $parentType;

        return $this;
    }

    /**
     * Get parent type
     *
     * @return string
     */
    public function getParentType()
    {
        return $this->parentType;
    }

    /**
     * Set child type
     *
     * @param string $childType
     *
     * @return self
     */
    public function setChildType($childType = null)
    {
        $this->childType = $childType;

        return $this;
    }

    /**
     * Get child type
     *
     * @return string
     */
    public function getChildType()
    {
        return $this->childType;
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
