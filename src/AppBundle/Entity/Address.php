<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Address
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Address",
 *      options={"collate"="utf8_swedish_ci"},
 *      indexes={
 *          @ORM\Index(
 *              name="idx_entry_id",
 *              columns={"entry_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_class",
 *              columns={"class"}
 *          ),
 *          @ORM\Index(
 *              name="idx_name",
 *              columns={"name"}
 *          ),
 *          @ORM\Index(
 *              name="idx_street",
 *              columns={"street"}
 *          ),
 *          @ORM\Index(
 *              name="idx_postalCode",
 *              columns={"postalCode"}
 *          ),
 *          @ORM\Index(
 *              name="idx_town",
 *              columns={"town"}
 *          ),
 *          @ORM\Index(
 *              name="idx_country",
 *              columns={"country"}
 *          ),
 *          @ORM\Index(
 *              name="idx_email",
 *              columns={"email"}
 *          ),
 *          @ORM\Index(
 *              name="idx_phone",
 *              columns={"phone"}
 *          ),
 *          @ORM\Index(
 *              name="idx_mobile",
 *              columns={"mobile"}
 *          )
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\AddressRepository"
 * )
 */
class Address extends Entity
{
    const CLASS_PRIMARY = 'PRIMARY';
    const CLASS_INVOICE = 'INVOICE';

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="class",
     *      type="string",
     *      nullable=true,
     *      columnDefinition="ENUM('PRIMARY','INVOICE')"
     * )
     * @Assert\Choice(
     *      choices={"PRIMARY","INVOICE"}
     * )
     */
    protected $class;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="name",
     *      type="string",
     *      length=128,
     *      nullable=true
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 128
     * )
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="street",
     *      type="string",
     *      length=128,
     *      nullable=true
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 128
     * )
     */
    protected $street;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="postalCode",
     *      type="string",
     *      length=32,
     *      nullable=true
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 32
     * )
     */
    protected $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="town",
     *      type="string",
     *      length=64,
     *      nullable=true
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 64
     * )
     */
    protected $town;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="country",
     *      type="string",
     *      length=64,
     *      nullable=true
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 64
     * )
     */
    protected $country;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="email",
     *      type="string",
     *      length=128,
     *      nullable=true
     * )
     * @Assert\Length(
     *      min = 3,
     *      max = 128
     * )
     * @Assert\Email()
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="phone",
     *      type="string",
     *      length=32,
     *      nullable=true
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 32
     * )
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="mobile",
     *      type="string",
     *      length=32,
     *      nullable=true
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 32
     * )
     */
    protected $mobile;

    /**
     * @var Entry
     *
     * @ORM\ManyToOne(
     *      targetEntity="Entry",
     *      inversedBy="addresses"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="entry_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    protected $entry;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *      targetEntity="Directory",
     *      mappedBy="addresses"
     * )
     */
    protected $directories;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->directories = new ArrayCollection();
    }

    /**
     * Set class
     *
     * @param string $class
     *
     * @return self
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
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
     * Set street
     *
     * @param string $street
     *
     * @return self
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set postal code
     *
     * @param string $postalCode
     *
     * @return self
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postal code
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set town
     *
     * @param string $town
     *
     * @return self
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * Get town
     *
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return self
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set email address
     *
     * @param string $email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email address
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone number
     *
     * @param string $phone
     *
     * @return self
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone number
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set mobile number
     *
     * @param string $mobile
     *
     * @return self
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile number
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
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
     * Add directory
     *
     * @param Directory $directory
     *
     * @return self
     */
    public function addDirectory(Directory $directory)
    {
        $this->directories[] = $directory;

        return $this;
    }

    /**
     * Remove directory
     *
     * @param Directory $directory
     */
    public function removeDirectory(Directory $directory)
    {
        $this->directories->removeElement($directory);
    }

    /**
     * Get directories
     *
     * @return ArrayCollection
     */
    public function getDirectories()
    {
        return $this->directories;
    }

    /**
     * JSON serialize
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'entry' => ($this->entry ? $this->entry->getId() : null),
            'name' => $this->name,
            'street' => $this->street,
            'postalCode' => $this->postalCode,
            'town' => $this->town,
            'country' => $this->country,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
        ];
    }
}
