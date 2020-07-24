<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Datagrid\Attribute;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;

class FamilyFilter extends ChoiceFilter
{
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);

        if (!$data || !isset($data['value']) || !is_array($data['value'])) {
            return false;
        }

        $qb = $ds->getQueryBuilder();
        $rootAlias = current($qb->getRootAliases());

        $qb
            ->innerJoin($rootAlias . '.families', 'f', 'WITH', 'f.id IN(:families)')
            ->setParameter(':families', $data['value']);

        return true;
    }
}
