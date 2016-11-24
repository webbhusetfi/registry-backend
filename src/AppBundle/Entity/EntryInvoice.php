<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EntryInvoice
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="EntryInvoice",
 *      options={"collate"="utf8_swedish_ci"},
 *      indexes={
 *          @ORM\Index(
 *              name="idx_paid",
 *              columns={"paid"}
 *          ),
 *          @ORM\Index(
 *              name="idx_entry_id",
 *              columns={"entry_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_invoice_id",
 *              columns={"invoice_id"}
 *          )
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\EntryInvoiceRepository"
 * )
 */
class EntryInvoice extends Entity
{
    /**
     * @var boolean
     *
     * @ORM\Column(
     *      name="paid",
     *      type="boolean",
     *      nullable=false,
     *      options={"unsigned"=true},
     *      columnDefinition="TINYINT(1) UNSIGNED NOT NULL"
     * )
     */
    protected $paid;

    /**
     * @var Entry
     *
     * @ORM\ManyToOne(
     *      targetEntity="Entry",
     *      inversedBy="entryInvoices"
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
     * @var Invoice
     *
     * @ORM\ManyToOne(
     *      targetEntity="Invoice",
     *      inversedBy="entryInvoices"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="invoice_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    protected $invoice;

    /**
     * Set paid
     *
     * @param boolean $paid
     *
     * @return self
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * Is paid
     *
     * @return boolean
     */
    public function isPaid()
    {
        return $this->paid;
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
     * Set invoice
     *
     * @param Invoice $invoice
     *
     * @return self
     */
    public function setInvoice(Invoice $invoice = null)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get invoice
     *
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }
}
