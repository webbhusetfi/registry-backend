<?php

namespace AppBundle\Security\User;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User provider for doctrine.
 *
 * Provides easy to use provisioning for Doctrine users.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class UserProvider implements UserProviderInterface
{
    private $registry;
    private $classOrAlias;
    private $property;
    private $managerName;

    public function __construct(ManagerRegistry $registry, $classOrAlias, $property = null, $managerName = null)
    {
        $this->registry = $registry;
        $this->classOrAlias = $classOrAlias;
        $this->property = $property;
        $this->managerName = $managerName;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        if (!isset($this->property)) {
            throw new \InvalidArgumentException('You must set the "property" option in the entity provider configuration.');
        }

        $qb = $this->getRepository()->createQueryBuilder('user');
        $qb->andWhere($qb->expr()->eq("user.{$this->property}", ':username'));
        $qb->setParameter('username', $username);
        $data = $qb->getQuery()
            ->setHint(Query::HINT_INCLUDE_META_COLUMNS, true)
            ->getArrayResult();

        if (count($data) !== 1) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        return new User($data[0]);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof \AppBundle\Security\User\User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }
        $user->setManager($this->getManager());

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === 'AppBundle\Security\User\User';
    }

    private function getManager()
    {
        return $this->registry->getManager($this->managerName);
    }

    private function getRepository()
    {
        return $this->getManager()->getRepository($this->classOrAlias);
    }
}
