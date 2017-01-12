<?php
namespace AppBundle\Entity\Common\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Organization trait
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait OrganizationTrait
{
    use NameTrait;
    use DescriptionTrait;

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
     *      length=255
     * )
     * @Assert\Length(max = 255)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="bank",
     *      type="string",
     *      length=64
     * )
     * @Assert\Length(max = 64)
     */
    protected $bank;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="account",
     *      type="string",
     *      length=64
     * )
     * @Assert\Length(max = 64)
     */
    protected $account;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="vat",
     *      type="string",
     *      length=64
     * )
     * @Assert\Length(max = 64)
     */
    protected $vat;

    /**
     * @inheritdoc
     */
    public function setBank($bank)
    {
        $this->bank = $bank;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @inheritdoc
     */
    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @inheritdoc
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVat()
    {
        return $this->vat;
    }
}
