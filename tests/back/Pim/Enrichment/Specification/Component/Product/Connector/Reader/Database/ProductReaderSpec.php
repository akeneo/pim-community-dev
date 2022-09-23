<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\MetricConverter;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductReaderSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        MetricConverter $metricConverter,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ProductQueryBuilderInterface $pqb,
        ChannelInterface $mobile,
        CursorInterface $products,
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            $metricConverter,
        );

        $this->setStepExecution($stepExecution);

        $filters = [
            'data' => [
                [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
            ],
            'structure' => [
                'scope' => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ],
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $mobile->getCode()->willReturn('mobile');
        $channelRepository->findOneByIdentifier('mobile')->shouldBeCalled()->willReturn($mobile);

        $pqbFactory->create(['default_scope' => 'mobile'])->shouldBeCalled()->willReturn($pqb);
        $pqb->addFilter('enabled', '=', true, [])->shouldBeCalled()->willReturn($pqb);
        $pqb->execute()->shouldBeCalled()->willReturn($products);

        $products->rewind()->shouldBeCalledOnce();

        $this->initialize();
    }

    function it_counts_the_total_number_of_products(
        CursorInterface $products,
    ) {
        $products->count()->shouldBeCalled()->willReturn(120);

        $this->totalItems()->shouldReturn(120);
    }

    function it_reads_products(
        MetricConverter $metricConverter,
        StepExecution $stepExecution,
        ChannelInterface $mobile,
        CursorInterface $products,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
    ) {
        $products->next()->shouldBeCalledTimes(3);
        $products->valid()->shouldBeCalledTimes(4)->willReturn(true, true, true, false);
        $products->current()->shouldBeCalledTimes(3)->willReturn($product1, $product2, $product3);
        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(3);
        $metricConverter->convert(Argument::any(), $mobile)->shouldBeCalledTimes(3);

        $this->read()->shouldReturn($product1);
        $this->read()->shouldReturn($product2);
        $this->read()->shouldReturn($product3);
        $this->read()->shouldReturn(null);
    }
}
