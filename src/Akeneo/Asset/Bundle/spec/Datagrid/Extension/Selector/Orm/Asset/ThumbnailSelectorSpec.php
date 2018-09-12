<?php

namespace spec\Akeneo\Asset\Bundle\Datagrid\Extension\Selector\Orm\Asset;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datasource\Datasource;

class ThumbnailSelectorSpec extends ObjectBehavior
{
    function it_is_a_selector()
    {
        $this->shouldImplement('Oro\Bundle\PimDataGridBundle\Extension\Selector\SelectorInterface');
    }

    function it_applies_extra_join_to_query_builder(
        Datasource $datasource,
        DatagridConfiguration $config,
        QueryBuilder $qb
    ) {
        $datasource->getQueryBuilder()->willReturn($qb);
        $qb->getRootAlias()->willReturn('pa');

        $qb->leftJoin('pa.references', 'aReferences')->willReturn($qb);
        $qb->leftJoin('aReferences.variations', 'rVariations')->willReturn($qb);
        $qb->leftJoin('rVariations.file', 'vFile')->willReturn($qb);
        $qb->addSelect('aReferences')->willReturn($qb);
        $qb->addSelect('rVariations')->willReturn($qb);
        $qb->addSelect('vFile')->willReturn($qb);

        $this->apply($datasource, $config);
    }
}
