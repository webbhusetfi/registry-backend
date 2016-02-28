<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Type
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Type",
 *      options={"collate"="utf8_swedish_ci"},
 *      indexes={
 *          @ORM\Index(
 *              name="idx_registry_id",
 *              columns={"registry_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_name",
 *              columns={"name"}
 *          )
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\TypeRepository"
 * )
 */
class Type extends Entity
{
    const CLASS_ORGANIZATION = 'ORGANIZATION';
    const CLASS_PERSON = 'PERSON';
    const CLASS_PLACE = 'PLACE';

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="class",
     *      type="string",
     *      nullable=false,
     *      columnDefinition="ENUM('ORGANIZATION','PERSON','PLACE') NOT NULL"
     * )
     * @Assert\Choice(
     *      choices={"ORGANIZATION","PERSON","PLACE"}
     * )
     * @Assert\NotBlank()
     */
    protected $class;

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
     * @Assert\NotBlank()
     */
    protected $registry;

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
            'class' => $this->class,
            'name' => $this->name,
            'registry' => ($this->registry ? $this->registry->getId() : null)
        ];
    }
}

