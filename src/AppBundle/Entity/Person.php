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
 *          @ORM\Index(name="idx_gender", columns={"gender"}),
 *          @ORM\Index(name="idx_firstName", columns={"firstName"}),
 *          @ORM\Index(name="idx_lastName", columns={"lastName"}),
 *          @ORM\Index(name="idx_birthdate", columns={"birthdate"})
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\PersonRepository"
 * )
 */
class Person extends Entry
{
    /**
     * @var string
     *
     * @ORM\Column(
     *      name="gender",
     *      type="string",
     *      nullable=false,
     *      columnDefinition="ENUM('MALE','FEMALE') NOT NULL"
     * )
     * @Assert\Choice(choices = {"MALE", "FEMALE"})
     * @Assert\NotBlank
     */
    private $gender;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=64, nullable=false)
     * @Assert\Length(min = 3, max = 64)
     * @Assert\NotBlank
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=64, nullable=false)
     * @Assert\Length(min = 3, max = 64)
     * @Assert\NotBlank
     */
    private $lastName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthdate", type="date", nullable=false)
     * @Assert\Date
     * @Assert\NotBlank
     */
    private $birthdate;


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
     * Set birthdate
     *
     * @param \DateTime|string $birthdate
     *
     * @return self
     */
    public function setBirthdate($birthdate)
    {
        if ($birthdate instanceof \DateTime) {
            $this->birthdate = $birthdate;
        } else {
            $this->birthdate = new \DateTime($birthdate);
        }

        return $this;
    }

    /**
     * Get birthdate
     *
     * @return \DateTime
     */
    public function getBirthdate()
    {
        return $this->birthdate;
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
                'birthdate' => $this->birthdate->format(\DateTime::ISO8601)
            ]
        );
    }
}
