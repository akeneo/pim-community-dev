<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\Reader\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\Repository\VariationRepositoryInterface;

class VariationReaderSpec extends ObjectBehavior
{
    function let(VariationRepositoryInterface $variationRepository)
    {
        $this->beConstructedWith($variationRepository);
    }

    function it_is_a_reader()
    {
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_returns_a_variation(
        $variationRepository,
        VariationInterface $variation,
        StepExecution $stepExecution
    ) {
        $variationRepository->findAll()->willReturn([$variation]);
        $this->setStepExecution($stepExecution);
        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(1);

        $this->read()->shouldReturn($variation);
        $this->read()->shouldReturn(null);
    }
}
