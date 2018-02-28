<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\ORM;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use PimEnterprise\Bundle\WorkflowBundle\Datagrid\Normalizer\ProductProposalNormalizer;

class ProductDraftHydratorSpec extends ObjectBehavior
{
    function let(
        RequestParametersExtractorInterface $extractor,
        ProductProposalNormalizer $normalizer
    ) {
        $this->beConstructedWith($extractor, $normalizer);
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
