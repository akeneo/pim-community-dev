<?php


namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Doctrine\FieldSorterInterface;

/**
 * Is associated sorter
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsAssociatedSorter implements FieldSorterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var CatalogContext */
    protected $context;

    /**
     * @param QueryBuilder   $qb
     * @param CatalogContext $context
     */
    public function __construct(QueryBuilder $qb, CatalogContext $context)
    {
        $this->qb      = $qb;
        $this->context = $context;
    }

    /**
     * '@inheritdoc}
     */
    public function addFieldSorter($field, $direction)
    {
        $qb = $this->qb;

        /*
         * Here we can aggregate the collection like in
         * Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductMassActionRepository::findValuesCommonAttributeIds
         *
         * Pb: how to set the collection back in the query builder
         */

        return $this;
    }
}
