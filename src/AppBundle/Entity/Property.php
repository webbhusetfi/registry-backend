<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Property
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Property",
 *      indexes={
 *          @ORM\Index(name="idx_name", columns={"name"}),
 *          @ORM\Index(name="idx_category_id", columns={"category_id"}),
 *          @ORM\Index(name="idx_registry_id", columns={"registry_id"})
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\PropertyRepository"
 * )
 * @UniqueEntity(
 *      fields={"category", "registry"}
 * )
 */
class Property
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
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     * @Assert\Length(min = 3, max = 64)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="string", length=255, nullable=true)
     * @Assert\Length(min = 3, max = 255)
     */
    private $notes;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="category_id",
     *          referencedColumnName="id",
     *          nullable=true,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    private $category;

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
     */
    private $registry;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *      targetEntity="Association",
     *      mappedBy="properties"
     * )
     */
    private $associations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->associations = new ArrayCollection();
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
     * Set category
     *
     * @param Category $category
     *
     * @return self
     */
    public function setCategory(Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
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
     * Add association
     *
     * @param Association $association
     *
     * @return self
     */
    public function addAssociation(Association $association)
    {
        $this->associations[] = $association;

        return $this;
    }

    /**
     * Remove association
     *
     * @param Association $association
     */
    public function removeAssociation(Association $association)
    {
        $this->associations->removeElement($association);
    }

    /**
     * Get associations
     *
     * @return ArrayCollection
     */
    public function getAssociations()
    {
        return $this->associations;
    }
}

