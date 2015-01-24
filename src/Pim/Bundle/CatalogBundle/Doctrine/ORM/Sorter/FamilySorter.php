<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Query\Sorter\FieldSorterInterface;

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
        $rootAlias = $this->qb->getRootAlias();

        $prefix    = 'sorter';
        $field     = $prefix.'familyLabel';
        $family    = $prefix.'family';
        $trans     = $prefix.'familyTranslations';

        $this->qb
            ->leftJoin($rootAlias.'.family', $family)
            ->leftJoin($family.'.translations', $trans, 'WITH', $trans.'.locale = :dataLocale');
        $this->qb
            ->addSelect('COALESCE('.$trans.'.label, CONCAT(\'[\', '.$family.'.code, \']\')) as '.$field);

        $this->qb->addOrderBy($field, $direction);

        $idField = $this->qb->getRootAlias().'.id';
        $this->qb->addOrderBy($idField);

        return $this;
    }
}
