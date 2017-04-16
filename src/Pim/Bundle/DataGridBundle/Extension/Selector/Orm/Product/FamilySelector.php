<?php

namespace Pim\Bundle\DataGridBundle\Extension\Selector\Orm\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface;

/**
 * Product family selector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilySelector implements SelectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, DatagridConfiguration $configuration)
    {
        // TODO: to fix with TIP-664
        return;
        $esQb = $datasource->getQueryBuilder();
        $qb = $esQb->getStorageQb();

        $locale = $configuration->offsetGetByPath('[source][locale_code]');
        $rootAlias = $qb->getRootAlias();

        $qb
            ->leftJoin($rootAlias.'.family', 'family')
            ->leftJoin('family.translations', 'ft', 'WITH', 'ft.locale = :dataLocale')
            ->addSelect('COALESCE(NULLIF(ft.label, \'\'), CONCAT(\'[\', family.code, \']\')) as familyLabel')
            ->setParameter('dataLocale', $locale);
    }
}
