<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\Selector\Orm;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

class FlexibleValuesSelectorSpec extends ObjectBehavior
{
    function it_should_be_a_selector()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface');
    }

    function it_applies_join_on_datasource_query(OrmDatasource $datasource, DatagridConfiguration $configuration, QueryBuilder $queryBuilder)
    {
        $datasource->getQueryBuilder()->willReturn($queryBuilder);
        $configuration->offsetGetByPath('[source][displayed_attributes]')->willReturn([1, 2]);
        $queryBuilder->getRootAlias()->willReturn('p');
        $queryBuilder->leftJoin(
            'p.values',
            'values',
            'WITH',
            'values.attribute IN (:attributeIds) AND (values.locale = :dataLocale OR values.locale IS NULL) AND (values.scope = :scopeCode OR values.scope IS NULL)'
        )->willReturn($queryBuilder);
        $queryBuilder->addSelect('values')->willReturn($queryBuilder);
        $queryBuilder->leftJoin('values.attribute', 'attribute')->willReturn($queryBuilder);
        $queryBuilder->addSelect('attribute')->willReturn($queryBuilder);
        $queryBuilder->setParameter('attributeIds', [1, 2])->willReturn($queryBuilder);
        $this->apply($datasource, $configuration);
    }
}
