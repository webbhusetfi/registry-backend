<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Directory
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Directory",
 *      options={"collate"="utf8_swedish_ci"},
 *      indexes={
 *          @ORM\Index(
 *              name="idx_registry_id",
 *              columns={"registry_id"}
 *          )
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\DirectoryRepository"
 * )
 */
class Directory extends Entity
{
    const VIEW_ADDRESS = 'ADDRESS';
    const VIEW_EMAIL_PHONE = 'EMAIL_PHONE';
    const VIEW_ANY = 'ANY';

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="view",
     *      type="string",
     *      nullable=false,
     *      columnDefinition="ENUM('ADDRESS','EMAIL_PHONE','ANY') NOT NULL"
     * )
     * @Assert\Choice(
     *      choices={"ADDRESS","EMAIL_PHONE","ANY"}
     * )
     * @Assert\NotBlank()
     */
    protected $view;

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
     *      min = 3,
     *      max = 64
     * )
     * @Assert\NotBlank()
     */
    protected $name;

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
    protected $addresses;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->addresses = new ArrayCollection();
    }

    /**
     * Set view
     *
     * @param string $view
     *
     * @return self
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Get view
     *
     * @return string
     */
    public function getView()
    {
        return $this->view;
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

    /**
     * JSON serialize
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'view' => $this->view,
            'name' => $this->name,
            'registry' => ($this->registry ? $this->registry->getId() : null),
        ];
    }
}
