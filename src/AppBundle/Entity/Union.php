<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Interfaces\OrganizationInterface;
use AppBundle\Entity\Common\Traits\OrganizationTrait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Union
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\UnionRepository"
 * )
 */
class Union extends Entry implements OrganizationInterface
{
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
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

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
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->description;
    }

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
