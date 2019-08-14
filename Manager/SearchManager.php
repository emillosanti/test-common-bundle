<?php

namespace SAM\CommonBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserInterface;
use SAM\AddressBookBundle\Repository\ContactMergedRepositoryInterface;
use SAM\AddressBookBundle\Repository\ContactRepositoryInterface;
use SAM\CommonBundle\Entity\BusinessSector;
use SAM\SearchBundle\Manager\SearchEngineManager;

class SearchManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /** @var SearchEngineManager */
    private $searchEngineManager;

    /**
     * SearchManager constructor.
     *
     * @param ObjectManager $om
     * @param SearchEngineManager $searchEngineManager
     */
    public function __construct(ObjectManager $om, SearchEngineManager $searchEngineManager)
    {
        $this->om = $om;
        $this->searchEngineManager = $searchEngineManager;
    }

    /**
     * @param string $query
     * @param null $userId
     * @param array|null $categories
     * @param BusinessSector $sector
     * @param string $job
     * @param array|null $tags
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findContacts(
        $query = null,
        $userId = null,
        $categories = null,
        $sector = null,
        $job = null,
        $tags = null)
    {
        return $this->searchEngineManager->getRepository(ContactRepositoryInterface::class)
            ->getUnmergedContactsQuery($query, $userId, $categories, $sector, $job, $tags);
    }

    /**
     * @param $lastname
     * @param array $phones
     * @param array $emails
     * @return array
     */
    public function findSimilarContactMerged($lastname, $phones = [], $emails = [])
    {
        if ($phones && count($phones)) {
            $phones = $phones->map(function ($phone) {
                            return $phone->getNumber();
                        })->toArray();
        }

        if ($emails && count($emails)) {
            $emails = $emails->map(function ($email) {
                            return $email->getEmail();
                        })->toArray();
        }

        return $this->searchEngineManager->getRepository(ContactMergedRepositoryInterface::class)
            ->searchSimilarContactMerged($lastname, $phones, $emails);
    }
}
