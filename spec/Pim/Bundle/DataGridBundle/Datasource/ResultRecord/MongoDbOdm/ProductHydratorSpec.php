<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class ProductHydratorSpec extends ObjectBehavior
{
    function it_is_a_hydrator()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface');
    }

    function it_hydrates_a_result_record(Builder $builder, Query $query)
    {
        $options = [
            'locale_code' => 'en_US',
            'scope_code'  => 'print',
            'current_group_id'  => null,
            'attributes_configuration' => []
        ];

        $builder->getQuery()->willReturn($query);
        $builder->hydrate(false)->willReturn($builder);
        $query->execute()->willReturn([]);

        $this->hydrate($builder, $options);

    }
}
