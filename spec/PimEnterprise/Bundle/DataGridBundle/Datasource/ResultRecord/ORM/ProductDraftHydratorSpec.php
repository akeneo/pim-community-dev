<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\ORM;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\DataGridBundle\Datagrid\RequestParametersExtractorInterface;

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

    function it_hydrates_a_result_record(QueryBuilder $builder, AbstractQuery $query, $extractor)
    {
        $extractor->getParameter('dataLocale')->shouldBeCalled();
        $builder->getQuery()->willReturn($query);
        $query->execute()->willReturn([]);

        $this->hydrate($builder, []);
    }
}
