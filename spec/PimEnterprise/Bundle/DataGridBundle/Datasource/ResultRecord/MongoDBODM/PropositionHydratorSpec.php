<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use Pim\Bundle\DataGridBundle\Datagrid\RequestParametersExtractorInterface;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class PropositionHydratorSpec extends ObjectBehavior
{
    function let(RequestParametersExtractorInterface $extractor)
    {
        $this->beConstructedWith($extractor);
    }

    function it_is_a_hydrator()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface');
    }

    function it_hydrates_a_result_record(Builder $builder, Query $query, RequestParametersExtractorInterface $extractor)
    {
        $extractor->getParameter('dataLocale')->shouldBeCalled();
        $builder->getQuery()->willReturn($query);
        $builder->hydrate(false)->willReturn($builder);
        $query->execute()->willReturn([]);

        $this->hydrate($builder, []);
    }
}
