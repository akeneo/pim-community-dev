<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\FieldSorterInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Family sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilySorter implements FieldSorterInterface
{
    /**
     * QueryBuilder
     * @var QueryBuilder
     */
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
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction)
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

        return $this;
    }
}
