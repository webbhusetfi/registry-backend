<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Person
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Person",
 *      indexes={
 *          @ORM\Index(
 *              name="idx_gender",
 *              columns={"gender"}
 *          ),
 *          @ORM\Index(
 *              name="idx_firstName",
 *              columns={"firstName"}
 *          ),
 *          @ORM\Index(
 *              name="idx_lastName",
 *              columns={"lastName"}
 *          ),
 *          @ORM\Index(
 *              name="idx_birthYear",
 *              columns={"birthYear"}
 *          ),
 *          @ORM\Index(
 *              name="idx_birthMonth",
 *              columns={"birthMonth"}
 *          ),
 *          @ORM\Index(
 *              name="idx_birthDay",
 *              columns={"birthDay"}
 *          )
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\PersonRepository"
 * )
 */
class Person extends Entry
{
    const GENDER_MALE = 'MALE';
    const GENDER_FEMALE = 'FEMALE';

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="gender",
     *      type="string",
     *      nullable=true,
     *      columnDefinition="ENUM('MALE','FEMALE')"
     * )
     * @Assert\Choice(
     *      choices={"MALE","FEMALE"}
     * )
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="firstName",
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
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="lastName",
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
    private $lastName;

    /**
     * @var integer
     *
     * @ORM\Column(
     *      name="birthYear",
     *      type="integer",
     *      nullable=true
     * )
     * @Assert\Type(
     *      type="integer"
     * )
     * @Assert\Range(
     *      min = 0,
     *      max = 9999
     * )
     */
    private $birthYear;

    /**
     * @var integer
     *
     * @ORM\Column(
     *      name="birthMonth",
     *      type="integer",
     *      nullable=true
     * )
     * @Assert\Type(
     *      type="integer"
     * )
     * @Assert\Range(
     *      min = 1,
     *      max = 12
     * )
     */
    private $birthMonth;

    /**
     * @var integer
     *
     * @ORM\Column(
     *      name="birthDay",
     *      type="integer",
     *      nullable=true
     * )
     * @Assert\Type(
     *      type="integer"
     * )
     * @Assert\Range(
     *      min = 1,
     *      max = 31
     * )
     */
    private $birthDay;

    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return self
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set first name
     *
     * @param string $firstName
     *
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set last name
     *
     * @param string $lastName
     *
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set birth year
     *
     * @param integer $birthYear
     *
     * @return self
     */
    public function setBirthYear($birthYear)
    {
        $this->birthYear = $birthYear;

        return $this;
    }

    /**
     * Get birth year
     *
     * @return integer
     */
    public function getBirthYear()
    {
        return $this->birthYear;
    }

    /**
     * Set birth month
     *
     * @param integer $birthMonth
     *
     * @return self
     */
    public function setBirthMonth($birthMonth)
    {
        $this->birthMonth = $birthMonth;

        return $this;
    }

    /**
     * Get birth month
     *
     * @return integer
     */
    public function getBirthMonth()
    {
        return $this->birthMonth;
    }

    /**
     * Set birth day
     *
     * @param integer $birthDay
     *
     * @return self
     */
    public function setBirthDay($birthDay)
    {
        $this->birthDay = $birthDay;

        return $this;
    }

    /**
     * Get birth day
     *
     * @return integer
     */
    public function getBirthDay()
    {
        return $this->birthDay;
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
                'class' => 'PERSON',
                'gender' => $this->gender,
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'birthYear' => $this->birthYear,
                'birthMonth' => $this->birthMonth,
                'birthDay' => $this->birthDay,
            ]
        );
    }
}
