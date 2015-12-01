<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use \JsonSerializable;

/**
 * Entry
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Entry",
 *      indexes={
 *          @ORM\Index(name="idx_externalId", columns={"externalId"}),
 *          @ORM\Index(name="idx_createdAt", columns={"createdAt"}),
 *          @ORM\Index(name="idx_registry_id", columns={"registry_id"}),
 *          @ORM\Index(name="idx_type_id", columns={"type_id"}),
 *          @ORM\Index(name="idx_status_id", columns={"status_id"}),
 *          @ORM\Index(name="idx_createdBy_id", columns={"createdBy_id"}),
 *          @ORM\Index(name="idx_class", columns={"class"})
 *      }
 * )
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(
 *      name="class",
 *      type="string",
 *      columnDefinition="ENUM('ORGANIZATION','PERSON','PLACE') NOT NULL"
 * )
 * @ORM\DiscriminatorMap({
 *      "ORGANIZATION" = "Organization",
 *      "PERSON" = "Person",
 *      "PLACE" = "Place"
 * })
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\EntryRepository"
 * )
 * @UniqueEntity(
 *      fields={"createdBy", "registry", "status", "type"}
 * )
 */
abstract class Entry implements JsonSerializable
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
     * @ORM\Column(
     *      name="externalId",
     *      type="string",
     *      length=255,
     *      nullable=true
     * )
     */
    private $externalId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime", nullable=false)
     * @Assert\DateTime()
     * @Assert\NotBlank
     */
    private $createdAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="createdBy_id",
     *          referencedColumnName="id",
     *          nullable=true,
     *          onDelete="SET NULL"
     *      )
     * })
     */
    private $createdBy;

    /**
     * @var Registry
     *
     * @ORM\ManyToOne(targetEntity="Registry")
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
     * @var Type
     *
     * @ORM\ManyToOne(targetEntity="Type")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="type_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="RESTRICT"
     *      )
     * })
     * @Assert\NotBlank
     */
    private $type;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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
     * JSON serialize
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'externalId' => $this->externalId,
            'createdAt' => $this->createdAt->format(\DateTime::ISO8601),
            'createdBy' => ($this->createdBy ? $this->createdBy->getId() : null),
            'registry' => ($this->registry ? $this->registry->getId() : null),
            'status' => ($this->status ? $this->status->getId() : null),
            'type' => ($this->type ? $this->type->getId() : null),
        ];
    }
}
