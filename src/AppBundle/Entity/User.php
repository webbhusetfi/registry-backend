<?php
namespace AppBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Role\Role;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use \JsonSerializable;

/**
 * User
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="User",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="UNIQUE",
 *              columns={
 *                  "username"
 *              }
 *          )
 *      },
 *      indexes={
 *          @ORM\Index(name="idx_entry_id", columns={"entry_id"}),
 *          @ORM\Index(name="idx_registry_id", columns={"registry_id"})
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\UserRepository"
 * )
 * @UniqueEntity("username")
 */
class User implements JsonSerializable, UserInterface
{
    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @var integer
     *
     * @ORM\Column(
     *      name="id",
     *      type="integer",
     *      nullable=false,
     *      options={"unsigned"=true}
     * )
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, nullable=false)
     * @Assert\Length(min = 5, max = 255)
     * @Assert\NotBlank
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=64, nullable=false)
     * @Assert\NotBlank
     */
    private $password;

    /**
     * @var Entry
     *
     * @ORM\ManyToOne(targetEntity="Entry")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="entry_id",
     *          referencedColumnName="id",
     *          nullable=true,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    private $entry;

    /**
     * @var Registry
     *
     * @ORM\ManyToOne(targetEntity="Registry")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="registry_id",
     *          referencedColumnName="id",
     *          nullable=true,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    private $registry;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
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
     * Set registry
     *
     * @param Registry $registry
     *
     * @return self
     */
    public function setRegistry(Registry $registry = null)
    {
        $this->registry = $registry;

        return $this;
    }

    /**
     * Get registry
     *
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * Returns the roles granted to the user.
     *
     * @return string[] The user roles
     */
    public function getRoles()
    {
        $roles = [self::ROLE_USER];
        if (!isset($this->entry)) {
            $roles[] = self::ROLE_ADMIN;
        }
        if (!isset($this->registry)) {
            $roles[] = self::ROLE_SUPER_ADMIN;
        }
        return $roles;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null  The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials()
    {

    }

    /**
     * JSON serialize
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'entry' => ($this->entry ? $this->entry->getId() : null),
            'registry' => ($this->registry ? $this->registry->getId() : null)
        ];
    }
}

