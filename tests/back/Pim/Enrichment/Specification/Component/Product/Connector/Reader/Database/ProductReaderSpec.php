<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\MetricConverter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Promise\ReturnPromise;

class ProductReaderSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        MetricConverter $metricConverter,
        ObjectDetacherInterface $objectDetacher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            $metricConverter,
            $objectDetacher
        );

        $this->setStepExecution($stepExecution);
    }

    function it_reads_products(
        $pqbFactory,
        $channelRepository,
        $metricConverter,
        $stepExecution,
        ChannelInterface $channel,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        JobParameters $jobParameters
    ) {
        $filters = [
            'data' => [
                [
                    'field'    => 'enabled',
                    'operator' => '=',
                    'value'    => true
                ],
                [
                    'field'    => 'family',
                    'operator' => 'IN',
                    'value'    => [
                        'camcorder'
                    ]
                ],
                [
                    'field'    => 'completeness',
                    'operator' => '>=',
                    'value'    => 100
                ]
            ],
            'structure' => [
                'scope'   => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ]
        ];

        $products = [$product1, $product2, $product3];
        $productsCount = count($products);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCode()->willReturn('mobile');

        $pqbFactory->create(['default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);
        $pqb->addFilter('enabled', Operators::EQUALS, true, [])->shouldBeCalled();
        $pqb->addFilter('family', Operators::IN_LIST, ['camcorder'], [])->shouldBeCalled();
        $pqb->addFilter('completeness', Operators::GREATER_OR_EQUAL_THAN, 100, [])->shouldBeCalled();
        $pqb->execute()
            ->shouldBeCalled()
            ->willReturn($cursor);

        $cursor->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $cursor->current()->will(new ReturnPromise($products));
        $cursor->next()->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(3);
        $metricConverter->convert(Argument::any(), $channel)->shouldBeCalledTimes(3);

        $this->initialize();
        $this->read()->shouldReturn($product1);
        $this->read()->shouldReturn($product2);
        $this->read()->shouldReturn($product3);
        $this->read()->shouldReturn(null);
    }

    function it_calculates_completeness_for_family(
        $pqbFactory,
        $channelRepository,
        $metricConverter,
        $stepExecution,
        ChannelInterface $channel,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        JobParameters $jobParameters
    ) {
        $filters = [
            'data' => [
                [
                    'field'    => 'completeness',
                    'operator' => '>=',
                    'value'    => 100
                ]
            ],
            'structure' => [
                'scope'   => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ]
        ];

        $products = [$product1, $product2, $product3];
        $productsCount = count($products);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCode()->willReturn('mobile');

        $pqbFactory->create(['default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);
        $pqb->addFilter('completeness', Operators::GREATER_OR_EQUAL_THAN, 100, [])->shouldBeCalled();
        $pqb->execute()
            ->shouldBeCalled()
            ->willReturn($cursor);


        $cursor->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $cursor->current()->will(new ReturnPromise($products));
        $cursor->next()->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(3);
        $metricConverter->convert(Argument::any(), $channel)->shouldBeCalledTimes(3);

        $this->initialize();
        $this->read()->shouldReturn($product1);
        $this->read()->shouldReturn($product2);
        $this->read()->shouldReturn($product3);
        $this->read()->shouldReturn(null);
    }

    function it_only_calculates_with_completeness_condition(
        $pqbFactory,
        $channelRepository,
        $metricConverter,
        $stepExecution,
        ChannelInterface $channel,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        JobParameters $jobParameters
    ) {
        $filters = [
            'data' => [
                [
                    'field'    => 'completeness',
                    'operator' => '>=',
                    'value'    => 100
                ]
            ],
            'structure' => [
                'scope'   => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ]
        ];

        $products = [$product1, $product2, $product3];
        $productsCount = count($products);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCode()->willReturn('mobile');

        $pqbFactory->create(['default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);
        $pqb->addFilter('completeness', Operators::GREATER_OR_EQUAL_THAN, 100, [])->shouldBeCalled();
        $pqb->execute()
            ->shouldBeCalled()
            ->willReturn($cursor);

        $cursor->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $cursor->current()->will(new ReturnPromise($products));
        $cursor->next()->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(3);
        $metricConverter->convert(Argument::any(), $channel)->shouldBeCalledTimes(3);

        $this->initialize();
        $this->read()->shouldReturn($product1);
        $this->read()->shouldReturn($product2);
        $this->read()->shouldReturn($product3);
        $this->read()->shouldReturn(null);
    }
}
