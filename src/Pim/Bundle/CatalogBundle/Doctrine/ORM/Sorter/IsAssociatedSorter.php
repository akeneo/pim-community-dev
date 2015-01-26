<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Query\Sorter\FieldSorterInterface;

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
        return $field === 'is_associated';
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction, $locale = null, $scope = null)
    {
        $this->qb->addOrderBy($field, $direction);

        $idField = $this->qb->getRootAlias().'.id';
        $this->qb->addOrderBy($idField);

        return $this;
    }
}
