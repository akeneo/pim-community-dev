<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Extension\Selector\Orm\Asset;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\Datasource;

class ThumbnailSelectorSpec extends ObjectBehavior
{
    function it_is_a_selector()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface');
    }

    function it_applies_extra_join_to_query_builder(
        Datasource $datasource,
        DatagridConfiguration $config,
        QueryBuilder $qb
    ) {
        $datasource->getQueryBuilder()->willReturn($qb);
        $qb->getRootAlias()->willReturn('pa');

        $qb->leftJoin('pa.references', 'aReferences')->willReturn($qb);
        $qb->leftJoin('aReferences.file', 'aReferencesFile')->willReturn($qb);
        $qb->addSelect('aReferencesFile')->willReturn($qb);
        $qb->addSelect('aReferences')->willReturn($qb);

        $this->apply($datasource, $config);
    }
}
