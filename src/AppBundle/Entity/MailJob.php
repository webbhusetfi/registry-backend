<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use AppBundle\Entity\Common\Type\AtomDateTime\AtomDateTime;

use AppBundle\Entity\Common\Interfaces\CreatedAtInterface;
use AppBundle\Entity\Common\Traits\CreatedAtTrait;

use AppBundle\Entity\Common\Interfaces\EntryInterface;
use AppBundle\Entity\Common\Traits\EntryTrait;

/**
 * MailJob
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="MailJob",
 *      options={"collate"="utf8_swedish_ci"},
 *      indexes={
 *          @ORM\Index(
 *              name="idx_entry_id",
 *              columns={"entry_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_transactionId",
 *              columns={"transactionId"}
 *          ),
 *          @ORM\Index(
 *              name="idx_messageId",
 *              columns={"messageId"}
 *          )
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\MailJobRepository"
 * )
 */
class MailJob extends Entity implements EntryInterface, CreatedAtInterface
{
    use EntryTrait;
    use CreatedAtTrait;

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
     * @var string
     *
     * @ORM\Column(
     *      name="transactionId",
     *      type="string",
     *      length=255,
     *      nullable=false
     * )
     */
    protected $transactionId;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="messageId",
     *      type="string",
     *      length=255,
     *      nullable=false
     * )
     */
    protected $messageId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(
     *      name="createdAt",
     *      type="atomdatetime",
     *      nullable=false
     * )
     * @Assert\DateTime()
     * @Assert\NotBlank()
     */
    protected $createdAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new AtomDateTime();
    }

    /**
     * Set transaction ID
     *
     * @param string $transactionId
     *
     * @return self
     */
    public function setTransactionId($transactionId = null)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    /**
     * Get transaction ID
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Set message ID
     *
     * @param string $messageId
     *
     * @return self
     */
    public function setMessageId($messageId = null)
    {
        $this->messageId = $messageId;

        return $this;
    }

    /**
     * Get message ID
     *
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }
}

