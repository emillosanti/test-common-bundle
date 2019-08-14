<?php

namespace SAM\CommonBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;

class SearchHitManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    private $entities;

    /**
     * SearchManager constructor.
     *
     * @param ObjectManager $om
     */
    public function __construct($entities, ObjectManager $om)
    {
        $this->om = $om;
        $this->entities = $entities;
    }

    /**
     * Register a new search hit
     * @param  string $query  
     * @param  string $bundle 
     * @param  string $target        
     */
    public function registerHit($query, $bundle, $target, $count = 1)
    {
        $searchHit = new $this->entities['search_hit']['class']();
        $searchHit->setQuery($query)
            ->setBundle($bundle)
            ->setTarget($target);

        if ($count > 1) {
            for ($i=0; $i<$count; $i++) {
                $this->om->persist(clone $searchHit);
            }
        }

        $this->om->persist($searchHit);
        $this->om->flush();
    }
}
