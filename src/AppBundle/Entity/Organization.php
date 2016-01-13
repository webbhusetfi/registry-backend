<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Organization
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Organization",
 *      indexes={
 *          @ORM\Index(
 *              name="idx_name",
 *              columns={"name"}
 *          )
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\OrganizationRepository"
 * )
 */
class Organization extends Entry
{
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
     *      min = 1,
     *      max = 64
     * )
     * @Assert\NotBlank()
     */
    private $name;

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
    private $description;

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
    private $bank;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="account",
     *      type="string",
     *      length=64,
     *      nullable=true
     * )
     * @Assert\Length(max = 64)
     */
    private $account;

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
    private $vat;

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
     * Set bank
     *
     * @param string $bank
     *
     * @return self
     */
    public function setBank($bank)
    {
        $this->bank = $bank;

        return $this;
    }

    /**
     * Get bank
     *
     * @return string
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * Set bank account
     *
     * @param string $account
     *
     * @return self
     */
    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get bank account
     *
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set VAT-number
     *
     * @param string $vat
     *
     * @return self
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * Get VAT-number
     *
     * @return string
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * JSON serialize
     *
     * @return array
     */
    public function jsonSerialize() {
        return array_merge(
            parent::jsonSerialize(),
            [
                'class' => 'ORGANIZATION',
                'name' => $this->name,
                'bank' => $this->bank,
                'account' => $this->account,
                'vat' => $this->vat,
            ]
        );
    }
}
