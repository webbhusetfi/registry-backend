<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * History
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="History",
 *      options={"collate"="utf8_swedish_ci"},
 *      indexes={
 *          @ORM\Index(
 *              name="idx_entry_id",
 *              columns={"entry_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_modifiedAt",
 *              columns={"modifiedAt"}
 *          ),
 *          @ORM\Index(
 *              name="idx_modifiedBy_id",
 *              columns={"modifiedBy_id"}
 *          )
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\HistoryRepository"
 * )
 */
class History extends Entity
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(
     *      name="modifiedAt",
     *      type="atomdatetime",
     *      nullable=false
     * )
     * @Assert\DateTime()
     * @Assert\NotBlank()
     */
    protected $modifiedAt;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="description",
     *      type="string",
     *      length=255,
     *      nullable=true
     * )
     * @Assert\Length(
     *      min = 3,
     *      max = 255
     * )
     */
    protected $description;

    /**
     * @var Entry
     *
     * @ORM\ManyToOne(
     *      targetEntity="Entry"
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
     * @var User
     *
     * @ORM\ManyToOne(
     *      targetEntity="User"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="modifiedBy_id",
     *          referencedColumnName="id",
     *          nullable=true,
     *          onDelete="SET NULL"
     *      )
     * })
     */
    protected $modifiedBy;

    /**
     * Set modified at
     *
     * @param \DateTime $modifiedAt
     *
     * @return self
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    /**
     * Get modified at
     *
     * @return \DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * Set modified by
     *
     * @param User $modifiedBy
     *
     * @return self
     */
    public function setModifiedBy(User $modifiedBy = null)
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    /**
     * Get modified by
     *
     * @return User
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }
}

