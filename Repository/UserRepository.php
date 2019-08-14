<?php

namespace SAM\CommonBundle\Repository;

use SAM\AddressBookBundle\Entity\ContactMerged;
use AppBundle\Entity\User;
use SAM\AddressBookBundle\Entity\UserReadContactMerged;
use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Model\UserInterface;

/**
 * Class UserRepository
 */
class UserRepository extends EntityRepository
{
    public function findByMailPatternsAndQuery($query, $mailPatterns)
    {
        $mailPatternClause = [];
        foreach ($mailPatterns as $mailPattern) {
            $mailPatternClause[] = 'email LIKE %'.$mailPattern;
        }

        $qb = $this->createQueryBuilder('u')
                    ->where('u.lastName LIKE :query OR u.firstName LIKE :query')
                    ->andWhere('u.enabled = :enabled')
                    ->setParameters(
                        [
                            'query' => $query . '%',
                            'enabled' => true
                        ]
                    );

        $orX = $qb->expr()->orX();
        foreach ($mailPatterns as $index => $mailPattern) {
            $orX->add('u.email LIKE :mailPattern'.$index);
            $qb->setParameter('mailPattern'.$index, '%'.$mailPattern);
        }
        $qb->andWhere($orX);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param ContactMerged $contact
     * @param User|null     $excluded
     *
     * @return User[]
     */
    public function findWithMergedContactAccess(ContactMerged $contact, $excluded = null)
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.readContactMerged', 'r')
            ->where('r.state = :state')
            ->andWhere('r.contactMerged = :contactMerged')
            ->orWhere('u.roles LIKE :roleAdmin')
            ->setParameter('state', UserReadContactMerged::STATE_VALIDATED)
            ->setParameter('contactMerged', $contact)
            ->setParameter('roleAdmin', '%ROLE_ADMIN%')
        ;
        if ($excluded instanceof UserInterface) {
            $qb->andWhere('u != :excluded')->setParameter('excluded', $excluded);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find contact owners
     * @param  ContactMerged $contact [description]
     * @return [type]                 [description]
     */
    public function findOwners(ContactMerged $contact)
    {
        $users = null;

        foreach ($contact->getContacts() as $contact) {
            $users[] = $contact->getUser();
        }

        return $users && count($users) > 0 ? array_unique($users) : $users;
    }

    /**
     * @param string $query
     *
     * @return array
     */
    public function findByName($query)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.lastName LIKE :query')
            ->orWhere('u.firstName LIKE :query')
            ->andWhere('u.enabled = :enabled')
            ->setParameters(
                [
                    'query' => $query . '%',
                    'enabled' => true,
                ]
            );

        return $qb->getQuery()->getResult();
    }
}
