<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\DataGrid\Extension\Selector\Orm\ProductValue;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\Datasource;
use Pim\Bundle\DataGridBundle\Extension\Selector\Orm\ProductValue\BaseSelector;

class AssetsSelectorSpec extends ObjectBehavior
{
    function let(BaseSelector $predecessor)
    {
        $this->beConstructedWith($predecessor);
    }

    function it_is_a_selector()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface');
    }

    function it_applies_join_on_datasource_query_builder(
        Datasource $datasource,
        DatagridConfiguration $configuration,
        QueryBuilder $queryBuilder
    ) {
        $datasource->getQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->leftJoin('values.assets', 'multiassets')->willReturn($queryBuilder);
        $queryBuilder->addSelect('multiassets')->willReturn($queryBuilder);

        $this->apply($datasource, $configuration);
    }
}
