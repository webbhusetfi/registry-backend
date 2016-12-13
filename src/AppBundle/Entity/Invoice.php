<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Entity;

use AppBundle\Entity\Common\Interfaces\AmountInterface;
use AppBundle\Entity\Common\Traits\AmountTrait;

use AppBundle\Entity\Common\Interfaces\BankAccountInterface;
use AppBundle\Entity\Common\Traits\BankAccountTrait;

use AppBundle\Entity\Common\Interfaces\BankInterface;
use AppBundle\Entity\Common\Traits\BankTrait;

use AppBundle\Entity\Common\Interfaces\CreatedAtInterface;
use AppBundle\Entity\Common\Traits\CreatedAtTrait;

use AppBundle\Entity\Common\Interfaces\DescriptionInterface;
use AppBundle\Entity\Common\Traits\DescriptionTrait;

use AppBundle\Entity\Common\Interfaces\DueAtInterface;
use AppBundle\Entity\Common\Traits\DueAtTrait;

use AppBundle\Entity\Common\Interfaces\EntryInterface;
use AppBundle\Entity\Common\Traits\EntryTrait;

use AppBundle\Entity\Common\Interfaces\MessageInterface;
use AppBundle\Entity\Common\Traits\MessageTrait;

use AppBundle\Entity\Common\Interfaces\NameInterface;
use AppBundle\Entity\Common\Traits\NameTrait;

use AppBundle\Entity\Common\Interfaces\VatInterface;
use AppBundle\Entity\Common\Traits\VatTrait;

use AppBundle\Entity\Common\Type\AtomDateTime\AtomDateTime;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Invoice
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Invoice",
 *      options={"collate"="utf8_swedish_ci"},
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\InvoiceRepository"
 * )
 */
class Invoice extends Entity implements EntryInterface, NameInterface,
    DescriptionInterface, MessageInterface, BankInterface, BankAccountInterface,
    VatInterface, AmountInterface, DueAtInterface, CreatedAtInterface
{
    use EntryTrait;
    use NameTrait;
    use DescriptionTrait;
    use MessageTrait;
    use BankTrait;
    use BankAccountTrait;
    use VatTrait;
    use AmountTrait;
    use DueAtTrait;
    use CreatedAtTrait;

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
     * @Assert\NotBlank()
     * @Assert\Expression(
     *     "value.getClassName() in ['AppBundle\\Entity\\Union', 'AppBundle\\Entity\\Association']",
     *     message="Invalid entry"
     * )
     */
    protected $entry;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="name",
     *      type="string",
     *      length=64
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 64
     * )
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="description",
     *      type="string",
     *      length=255,
     *      nullable=true
     * )
     * @Assert\Length(max = 255)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="message",
     *      type="text",
     *      nullable=true
     * )
     */
    protected $message;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="bank",
     *      type="string",
     *      length=64,
     *      nullable=true
     * )
     * @Assert\Length(max = 64)
     */
    protected $bank;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="bankAccount",
     *      type="string",
     *      length=64,
     *      nullable=true
     * )
     * @Assert\Length(max = 64)
     */
    protected $bankAccount;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="vat",
     *      type="string",
     *      length=64,
     *      nullable=true
     * )
     * @Assert\Length(max = 64)
     */
    protected $vat;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="amount",
     *      type="string",
     *      length=16,
     *      nullable=false
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 16
     * )
     * @Assert\NotBlank()
     */
    protected $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(
     *      name="dueAt",
     *      type="atomdatetime",
     *      nullable=true
     * )
     * @Assert\DateTime()
     */
    protected $dueAt;

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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="EntryInvoice",
     *      mappedBy="invoice"
     * )
     */
    protected $entryInvoices;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new AtomDateTime();
        $this->entryInvoices = new ArrayCollection();
    }

    /**
     * Get entry invoices
     *
     * @return ArrayCollection
     */
    public function getEntryInvoices()
    {
        return $this->entryInvoices;
    }
}
