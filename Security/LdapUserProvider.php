<?php

namespace SAM\CommonBundle\Security;

use SAM\CommonBundle\Entity\User as UserEntity;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class LdapUserProvider implements UserProviderInterface
{
    private $userManager;
    private $om;
    private $ldap;
    private $baseDn;
    private $searchDn;
    private $searchPassword;
    private $defaultRoles;
    private $uidKey;
    private $defaultSearch;
    private $passwordAttribute;

    /**
     * @param UserManagerInterface $userManager
     * @param ObjectManager        $om
     * @param LdapInterface        $ldap
     * @param string               $baseDn
     * @param string               $searchDn
     * @param string               $searchPassword
     * @param array                $defaultRoles
     * @param string               $uidKey
     * @param string               $filter
     * @param string               $passwordAttribute
     */
    public function __construct(
        UserManagerInterface $userManager,
        ObjectManager $om,
        LdapInterface $ldap,
        $baseDn,
        $searchDn = null,
        $searchPassword = null,
        array $defaultRoles = array(),
        $uidKey = 'sAMAccountName',
        $filter = '({uid_key}={username})',
        $passwordAttribute = null
    ) {
        if (null === $uidKey) {
            $uidKey = 'sAMAccountName';
        }

        $this->userManager = $userManager;
        $this->om = $om;
        $this->ldap = $ldap;
        $this->baseDn = $baseDn;
        $this->searchDn = $searchDn;
        $this->searchPassword = $searchPassword;
        $this->defaultRoles = $defaultRoles;
        $this->uidKey = $uidKey;
        $this->defaultSearch = str_replace('{uid_key}', $uidKey, $filter);
        $this->passwordAttribute = $passwordAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        try {
            $this->ldap->bind($this->searchDn, $this->searchPassword);
            $username = $this->ldap->escape($username, '', LdapInterface::ESCAPE_FILTER);
            $query = str_replace('{username}', $username, $this->defaultSearch);
            $search = $this->ldap->query($this->baseDn, $query);
        } catch (ConnectionException $e) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username), 0, $e);
        }

        $entries = $search->execute();
        $count = count($entries);

        if (!$count) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        if ($count > 1) {
            throw new UsernameNotFoundException('More than one user found');
        }

        $entry = $entries[0];

        try {
            if (null !== $this->uidKey) {
                $username = $this->getAttributeValue($entry, $this->uidKey);
            }
        } catch (InvalidArgumentException $e) {
        }

        return $this->loadUser($username, $entry);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof UserEntity) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $userRepository = $this->om->getRepository('user');
        $user = $userRepository->findOneBy(["username" => $user->getUsername()]);

        if ($user === null) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return UserEntity::class === $class;
    }

    /**
     * Loads a user from an LDAP entry.
     *
     * @param string $username
     * @param Entry  $entry
     *
     * @return User
     */
    protected function loadUser($username, Entry $entry)
    {
        $user = $this->om->getRepository('user')->findOneBy(['username' => $username]);
        if (null === $user) {
            $password = '';

            if (null !== $this->passwordAttribute) {
                $password = $this->getAttributeValue($entry, $this->passwordAttribute);
            }

            /** @var UserEntity|User $user */
            $user = $this->userManager->createUser();
            $user->setFirstname($entry->getAttribute("givenName")[0]);
            $user->setLastname($entry->getAttribute("sn")[0]);
            $user->setEmail($entry->getAttribute("mail")[0]);
            $user->setUsername($username);
            $user->setRoles($this->defaultRoles);
            $user->setPassword($password);
            $user->setEnabled(true);

            $this->om->persist($user);
            $this->om->flush();
        }

        return $user;
    }

    /**
     * Fetches a required unique attribute value from an LDAP entry.
     *
     * @param null|Entry $entry
     * @param string     $attribute
     */
    private function getAttributeValue(Entry $entry, $attribute)
    {
        if (!$entry->hasAttribute($attribute)) {
            throw new InvalidArgumentException(
                sprintf('Missing attribute "%s" for user "%s".', $attribute, $entry->getDn())
            );
        }

        $values = $entry->getAttribute($attribute);

        if (1 !== count($values)) {
            throw new InvalidArgumentException(sprintf('Attribute "%s" has multiple values.', $attribute));
        }

        return $values[0];
    }
}
