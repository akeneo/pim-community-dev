<?php

namespace Specification\Akeneo\Asset\Bundle\Datagrid\Datasource\ResultRecord\ORM;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;

class AssetHydratorSpec extends ObjectBehavior
{
    function it_is_a_hydrator()
    {
        $this->shouldImplement(HydratorInterface::class);
    }

    function it_hydrates_a_result_record(QueryBuilder $builder, AbstractQuery $query)
    {
        $builder->getQuery()->willReturn($query);
        $query->execute()->willReturn([]);

        $this->hydrate($builder, []);
    }
}
