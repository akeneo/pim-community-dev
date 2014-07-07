<?php

namespace PimEnterprise\Bundle\VersioningBundle\Doctrine\ORM;

use Pim\Bundle\VersioningBundle\Doctrine\ORM\VersionRepository as BaseVersionRepository;

/**
 * Version repository
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * TODO: find a way to inject the published product class
 */
class VersionRepository extends BaseVersionRepository
{
    /** @staticvar string */
    const PUBLISHED_PRODUCT_CLASS = 'PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProduct';

    /**
     * Query builder used for the product history grid
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createProductDatagridQueryBuilder()
    {
        $qb = $this->createDatagridQueryBuilder();

        $qb->addSelect(
            sprintf(
                '(SELECT p.id FROM %s p WHERE p.version = v) AS published_version_id',
                static::PUBLISHED_PRODUCT_CLASS
            )
        );

         return $qb;
    }
}
