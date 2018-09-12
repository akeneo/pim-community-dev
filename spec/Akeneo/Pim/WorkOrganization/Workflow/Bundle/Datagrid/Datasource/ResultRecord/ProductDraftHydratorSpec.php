<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Datasource\ResultRecord;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductDraftHydratorSpec extends ObjectBehavior
{
    function let(
        RequestParametersExtractorInterface $extractor,
        NormalizerInterface $normalizer
    ) {
        $this->beConstructedWith($extractor, $normalizer);
    }

    function it_is_a_hydrator()
    {
        $this->shouldImplement('Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface');
    }

    function it_hydrates_a_result_record(QueryBuilder $builder, AbstractQuery $query)
    {
        $builder->getQuery()->willReturn($query);
        $query->execute()->willReturn([]);

        $this->hydrate($builder, []);
    }
}
