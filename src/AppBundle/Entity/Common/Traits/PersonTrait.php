<?php
namespace AppBundle\Entity\Common\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Person trait
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait PersonTrait
{
    /**
     * @var string
     *
     * @ORM\Column(
     *      name="gender",
     *      type="string",
     *      columnDefinition="ENUM('MALE','FEMALE')"
     * )
     * @Assert\Choice(
     *      choices={"MALE","FEMALE"}
     * )
     */
    protected $gender;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="firstName",
     *      type="string",
     *      length=64
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 64
     * )
     * @Assert\NotBlank()
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="lastName",
     *      type="string",
     *      length=64
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 64
     * )
     * @Assert\NotBlank()
     */
    protected $lastName;

    /**
     * @var integer
     *
     * @ORM\Column(
     *      name="birthYear",
     *      type="integer"
     * )
     * @Assert\Type(
     *      type="integer"
     * )
     * @Assert\Range(
     *      min = 0,
     *      max = 9999
     * )
     */
    protected $birthYear;

    /**
     * @var integer
     *
     * @ORM\Column(
     *      name="birthMonth",
     *      type="integer"
     * )
     * @Assert\Type(
     *      type="integer"
     * )
     * @Assert\Range(
     *      min = 1,
     *      max = 12
     * )
     */
    protected $birthMonth;

    /**
     * @var integer
     *
     * @ORM\Column(
     *      name="birthDay",
     *      type="integer"
     * )
     * @Assert\Type(
     *      type="integer"
     * )
     * @Assert\Range(
     *      min = 1,
     *      max = 31
     * )
     */
    protected $birthDay;

    /**
     * @inheritdoc
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @inheritdoc
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @inheritdoc
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @inheritdoc
     */
    public function setBirthYear($birthYear)
    {
        $this->birthYear = $birthYear;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBirthYear()
    {
        return $this->birthYear;
    }

    /**
     * @inheritdoc
     */
    public function setBirthMonth($birthMonth)
    {
        $this->birthMonth = $birthMonth;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBirthMonth()
    {
        return $this->birthMonth;
    }

    /**
     * @inheritdoc
     */
    public function setBirthDay($birthDay)
    {
        $this->birthDay = $birthDay;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBirthDay()
    {
        return $this->birthDay;
    }
}
