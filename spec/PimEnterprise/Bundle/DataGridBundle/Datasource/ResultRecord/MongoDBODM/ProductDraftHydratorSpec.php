<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDBODM;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class ProductDraftHydratorSpec extends ObjectBehavior
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
        $query->execute()->willReturn([]);

        $this->hydrate($builder, []);
    }
}
