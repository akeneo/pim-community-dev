<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\Query\Sorter\FieldSorterInterface;

/**
 * Completeness sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessSorter implements FieldSorterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->qb = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return $field === 'completeness';
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction, $locale = null, $scope = null)
    {
        if (null === $locale || null === $scope) {
            throw new \InvalidArgumentException(
                'Cannot prepare condition on completenesses without locale and scope'
            );
        }

        $field = sprintf(
            "%s.%s.%s-%s",
            ProductQueryUtility::NORMALIZED_FIELD,
            'completenesses',
            $scope,
            $locale
        );
        $this->qb->sort($field, $direction);
        $this->qb->sort('_id');

        return $this;
    }
}
