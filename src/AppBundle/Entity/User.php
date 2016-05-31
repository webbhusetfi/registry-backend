<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Role\Role;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="User",
 *      options={"collate"="utf8_swedish_ci"},
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="UNIQUE",
 *              columns={"username"}
 *          )
 *      },
 *      indexes={
 *          @ORM\Index(
 *              name="idx_entry_id",
 *              columns={"entry_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_registry_id",
 *              columns={"registry_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_role",
 *              columns={"role"}
 *          )
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\UserRepository"
 * )
 */
class User extends Entity implements UserInterface
{
    const ROLE_USER = 'USER';
    const ROLE_ADMIN = 'ADMIN';
    const ROLE_SUPER_ADMIN = 'SUPER_ADMIN';

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="username",
     *      type="string",
     *      length=255,
     *      nullable=false
     * )
     * @Assert\Length(
     *      min = 5,
     *      max = 255
     * )
     * @Assert\NotBlank()
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="password",
     *      type="string",
     *      length=64,
     *      nullable=false
     * )
     * @Assert\NotBlank()
     */
    protected $password;

    /**
     * @var Entry
     *
     * @ORM\ManyToOne(
     *      targetEntity="Entry"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="entry_id",
     *          referencedColumnName="id",
     *          nullable=true,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    protected $entry;

    /**
     * @var Registry
     *
     * @ORM\ManyToOne(
     *      targetEntity="Registry"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="registry_id",
     *          referencedColumnName="id",
     *          nullable=true,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    protected $registry;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="role",
     *      type="string",
     *      nullable=false,
     *      columnDefinition="ENUM('SUPER_ADMIN','ADMIN','USER') NOT NULL"
     * )
     * @Assert\Choice(
     *      choices={"SUPER_ADMIN","ADMIN","USER"}
     * )
     * @Assert\NotBlank()
     */
    protected $role;

    /**
     * @var array
     */
    protected $roles;

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
     * Set role
     *
     * @param string $role
     *
     * @return self
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Returns all the roles granted to the user.
     *
     * @return string[] All roles
     */
    public function getAllRoles()
    {
        if (!isset($this->roles)) {
            $this->roles = [$this->role];
            if ($this->role == self::ROLE_SUPER_ADMIN) {
                $this->roles[] = self::ROLE_ADMIN;
                $this->roles[] = self::ROLE_USER;
            } elseif ($this->role == self::ROLE_ADMIN) {
                $this->roles[] = self::ROLE_USER;
            }
        }
        return $this->roles;
    }

    /**
     * Check if user is granted the supplied role.
     *
     * @return boolean
     */
    public function hasRole($role)
    {
        return in_array($role, $this->getAllRoles());
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
        return ['ROLE_' . $this->role];
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
            'role' => $this->role,
            'entry' => ($this->entry ? $this->entry->getId() : null),
            'registry' => ($this->registry ? $this->registry->getId() : null)
        ];
    }
}

