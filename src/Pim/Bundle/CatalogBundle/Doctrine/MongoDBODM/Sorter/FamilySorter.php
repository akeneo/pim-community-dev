<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\Query\Sorter\FieldSorterInterface;

/**
 * Family sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilySorter implements FieldSorterInterface
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
        return $field === 'family';
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction, $locale = null, $scope = null)
    {
        if (null === $locale) {
            throw new \InvalidArgumentException(
                'Cannot prepare condition on family sorter without locale'
            );
        }

        $fieldLabel = sprintf(
            "%s.%s.label.%s",
            ProductQueryUtility::NORMALIZED_FIELD,
            $field,
            $locale
        );
        $fieldCode = sprintf(
            "%s.%s.code",
            ProductQueryUtility::NORMALIZED_FIELD,
            $field
        );
        $this->qb->sort($fieldLabel, $direction);
        $this->qb->sort($fieldCode, $direction);
        $this->qb->sort('_id');

        return $this;
    }
}
