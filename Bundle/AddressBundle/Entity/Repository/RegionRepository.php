<?php

namespace Oro\Bundle\AddressBundle\Entity\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

class RegionRepository extends EntityRepository
{
    /**
     * @param Country $country
     * @return QueryBuilder
     */
    public function getCountryRegionsQueryBuilder(Country $country)
    {
        return $this->createQueryBuilder('r')
            ->where('r.country = :country')
            ->orderBy('r.name', 'ASC')
            ->setParameter('country', $country);
    }

    /**
     * @param Country $country
     * @return Region[]
     */
    public function getCountryRegions(Country $country)
    {
        $query = $this->getCountryRegionsQueryBuilder($country)->getQuery();
        $query->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );

        return $query->execute();
    }
}
