<?php

namespace Pim\Bundle\DataGridBundle\Extension\Sorter\Orm;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface;

/**
 * Product family sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFamilySorter implements SorterInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, $field, $direction)
    {
        $qb        = $datasource->getQueryBuilder();
        $rootAlias = $qb->getRootAlias();

        $prefix    = 'sorter';
        $field     = $prefix.$field;
        $family    = $prefix.'family';
        $trans     = $prefix.'familyTranslations';

        $qb
            ->leftJoin($rootAlias.'.family', $family)
            ->leftJoin($family.'.translations', $trans, 'WITH', $trans.'.locale = :dataLocale');
        $qb
            ->addSelect('COALESCE('.$trans.'.label, CONCAT(\'[\', '.$family.'.code, \']\')) as '.$field);

        $qb->addOrderBy($field, $direction);
    }
}
