<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\Selector\Orm\ProductValue;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\Datasource;
use Pim\Bundle\DataGridBundle\Extension\Selector\Orm\ProductValue\BaseSelector;

class OptionsSelectorSpec extends ObjectBehavior
{
    function let(BaseSelector $predecessor)
    {
        $this->beConstructedWith($predecessor);
    }

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
        $queryBuilder->leftJoin('values.options', 'multioptions')->willReturn($queryBuilder);
        $queryBuilder->addSelect('multioptions')->willReturn($queryBuilder);
        $queryBuilder->leftJoin(
            'multioptions.optionValues',
            'multioptionvalues',
            'WITH',
            'multioptionvalues.locale = :dataLocale OR multioptionvalues.locale IS NULL'
        )->willReturn($queryBuilder);
        $queryBuilder->addSelect('multioptionvalues')->willReturn($queryBuilder);
        $this->apply($datasource, $configuration);
    }
}
