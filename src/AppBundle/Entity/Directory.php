<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Directory
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Directory",
 *      indexes={
 *          @ORM\Index(name="idx_entry_id", columns={"entry_id"})
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\DirectoryRepository"
 * )
 * @UniqueEntity(
 *      fields={"entry"}
 * )
 */
class Directory
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
     *      name="type",
     *      type="string",
     *      nullable=false,
     *      columnDefinition="ENUM('ADDRESS','EMAIL')"
     * )
     * @Assert\Choice(choices = {"ADDRESS", "EMAIL"})
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     * @Assert\Length(min = 3, max = 64)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var Entry
     *
     * @ORM\ManyToOne(targetEntity="Entry")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="entry_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    private $entry;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *      targetEntity="Address",
     *      inversedBy="directories"
     * )
     * @ORM\JoinTable(
     *      name="DirectoryAddress",
     *      joinColumns={
     *          @ORM\JoinColumn(
     *              name="directory_id",
     *              referencedColumnName="id",
     *              nullable=false,
     *              onDelete="CASCADE"
     *          )
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(
     *              name="address_id",
     *              referencedColumnName="id",
     *              nullable=false,
     *              onDelete="CASCADE"
     *          )
     *      }
     * )
     */
    private $addresses;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->addresses = new ArrayCollection();
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
     * Set type
     *
     * @param string $type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * Set entry
     *
     * @param Entry $entry
     *
     * @return self
     */
    public function setEntry(Entry $entry = null)
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * Get entry
     *
     * @return Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * Add address
     *
     * @param Address $address
     *
     * @return self
     */
    public function addAddress(Address $address)
    {
        $this->addresses[] = $address;

        return $this;
    }

    /**
     * Remove address
     *
     * @param Address $address
     */
    public function removeAddress(Address $address)
    {
        $this->addresses->removeElement($address);
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
}

