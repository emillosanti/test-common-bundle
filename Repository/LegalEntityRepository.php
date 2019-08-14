<?php

namespace SAM\CommonBundle\Repository;

use Doctrine\ORM\EntityRepository;

class LegalEntityRepository extends EntityRepository
{
    public function findQueryBuilderParent()
    {
        return $this->createQueryBuilder('le')
            ->select('le')
            ->where('le.parent IS NULL')
        ;
    }

    public function findQueryBuilderChildren()
    {
    	return $this->createQueryBuilder('le')
            ->select('le')
            ->where('le.parent IS NOT NULL')
        ;
    }
}
