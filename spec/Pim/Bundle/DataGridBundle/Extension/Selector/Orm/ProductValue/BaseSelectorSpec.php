<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\Selector\Orm\ProductValue;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\Datasource;

class BaseSelectorSpec extends ObjectBehavior
{
    function it_is_a_selector()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface');
    }

    function it_applies_join_on_datasource_query(
        Datasource $datasource,
        DatagridConfiguration $configuration,
        QueryBuilder $queryBuilder
    ) {
        $datasource->getQueryBuilder()->willReturn($queryBuilder);
        $configuration->offsetGetByPath('[source][displayed_attribute_ids]')->willReturn([1, 2]);
        $queryBuilder->getRootAlias()->willReturn('p');
        $queryBuilder->leftJoin(
            'p.values',
            'values',
            'WITH',
            sprintf(
                '%s AND %s AND %s',
                'values.attribute IN (:attributeIds)',
                '(values.locale = :dataLocale OR values.locale IS NULL)',
                '(values.scope = :scopeCode OR values.scope IS NULL)'
            )
        )->willReturn($queryBuilder);
        $queryBuilder->addSelect('values')->willReturn($queryBuilder);
        $queryBuilder->leftJoin('values.attribute', 'attribute')->willReturn($queryBuilder);
        $queryBuilder->addSelect('attribute')->willReturn($queryBuilder);
        $queryBuilder->setParameter('attributeIds', [1, 2])->willReturn($queryBuilder);
        $this->apply($datasource, $configuration);
    }
}
