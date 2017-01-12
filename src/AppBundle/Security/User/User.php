<?php

namespace AppBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * User
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class User implements UserInterface, EquatableInterface, \Serializable,
    \JsonSerializable
{
    const ROLE_USER = 'USER';
    const ROLE_ADMIN = 'ADMIN';
    const ROLE_SUPER_ADMIN = 'SUPER_ADMIN';

    // Serialized properties
    private $id;
    private $username;
    private $password;
    private $role;
    private $registryId;
    private $entryId;

    // Other properties
    private $manager;
    private $roles;
    private $entry;
    private $registry;

    public function __construct(array $user)
    {
        $this->id = $user['id'];
        $this->username = $user['username'];
        $this->password = $user['password'];
        $this->role = $user['role'];
        $this->registryId = $user['registry_id'];
        $this->entryId = $user['entry_id'];
    }

    /**
     * Get doctrine object manager
     *
     * @return ObjectManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Set doctrine object manager
     *
     * @param ObjectManager $manager
     *
     * @return self
     */
    public function setManager(ObjectManager $manager)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Get ID
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return ['ROLE_' . $this->role];
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
     * Check if user is granted the supplied role.
     *
     * @return boolean
     */
    public function hasRole($role)
    {
        return in_array($role, $this->getAllRoles());
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
     * Get registry ID
     *
     * @return integer
     */
    public function getRegistryId()
    {
        return $this->registryId;
    }

    /**
     * Get registry
     *
     * @return Registry
     */
    public function getRegistry()
    {
        if (!isset($this->registry) && isset($this->registryId)) {
            $this->registry = $this->manager->getReference(
                'AppBundle\Entity\Registry',
                $this->registryId
            );
        }
        return $this->registry;
    }

    /**
     * Get entry ID
     *
     * @return integer
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * Get entry
     *
     * @return Entry
     */
    public function getEntry()
    {
        if (!isset($this->entry) && isset($this->entryId)) {
            $this->entry = $this->manager->getReference(
                'AppBundle\Entity\Entry',
                $this->entryId
            );
        }
        return $this->entry;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof self) {
            return false;
        }

        if ($this->id !== $user->getId()) {
            return false;
        }

        return true;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->role,
            $this->registryId,
            $this->entryId
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->role,
            $this->registryId,
            $this->entryId
        ) = unserialize($serialized);
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
            'entry' => $this->entryId,
            'registry' => $this->registryId
        ];
    }
}
