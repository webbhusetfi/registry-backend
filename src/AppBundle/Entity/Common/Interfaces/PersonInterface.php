<?php
namespace AppBundle\Entity\Common\Interfaces;

/**
 * Person interface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface PersonInterface
{
    const GENDER_MALE = 'MALE';
    const GENDER_FEMALE = 'FEMALE';

    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return self
     */
    public function setGender($gender);


    /**
     * Get gender
     *
     * @return string
     */
    public function getGender();

    /**
     * Set first name
     *
     * @param string $firstName
     *
     * @return self
     */
    public function setFirstName($firstName);

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Set last name
     *
     * @param string $lastName
     *
     * @return self
     */
    public function setLastName($lastName);

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName();

    /**
     * Set birth year
     *
     * @param integer $birthYear
     *
     * @return self
     */
    public function setBirthYear($birthYear);

    /**
     * Get birth year
     *
     * @return integer
     */
    public function getBirthYear();

    /**
     * Set birth month
     *
     * @param integer $birthMonth
     *
     * @return self
     */
    public function setBirthMonth($birthMonth);

    /**
     * Get birth month
     *
     * @return integer
     */
    public function getBirthMonth();

    /**
     * Set birth day
     *
     * @param integer $birthDay
     *
     * @return self
     */
    public function setBirthDay($birthDay);

    /**
     * Get birth day
     *
     * @return integer
     */
    public function getBirthDay();
}
