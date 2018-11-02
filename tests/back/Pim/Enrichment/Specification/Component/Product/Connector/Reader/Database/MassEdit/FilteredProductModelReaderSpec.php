<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit\FilteredProductModelReader;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\MetricConverter;
use Prophecy\Argument;
use Prophecy\Promise\ReturnPromise;

class FilteredProductModelReaderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FilteredProductModelReader::class);
    }

    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        MetricConverter $metricConverter,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            $metricConverter
        );

        $this->setStepExecution($stepExecution);
    }

    function it_set_step_execution(
        $stepExecution
    ) {
        $this->setStepExecution($stepExecution)->shouldReturn(null);
    }

    function it_reads_products_only_and_not_product_models(
        $pqbFactory,
        $channelRepository,
        $metricConverter,
        $stepExecution,
        ChannelInterface $channel,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductModelInterface $productModel3,
        JobParameters $jobParameters
    ) {
        $filters = [
            'data'      => [
                [
                    'field'    => 'enabled',
                    'operator' => '=',
                    'value'    => true,
                ],
                [
                    'field'    => 'family',
                    'operator' => 'IN',
                    'value'    => [
                        'camcorder',
                    ],
                ],
            ],
            'structure' => [
                'scope'   => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ],
        ];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCode()->willReturn('mobile');

        $pqbFactory->create(['filters' => array_merge($filters['data'], [[
            'field' => 'entity_type',
            'operator' => '=',
            'value' => ProductModelInterface::class,
        ]]), 'default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);
        $pqb->execute()
            ->shouldBeCalled()
            ->willReturn($cursor);

        $products = [$productModel1, $productModel2, $productModel3];
        $productsCount = count($products);
        $cursor->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $cursor->current()->will(new ReturnPromise($products));
        $cursor->next()->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(3);
        $metricConverter->convert(Argument::any(), $channel)->shouldBeCalledTimes(3);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(0);

        $this->initialize();
        $this->read()->shouldReturn($productModel1);
        $this->read()->shouldReturn($productModel2);
        $this->read()->shouldReturn($productModel3);
        $this->read()->shouldReturn(null);
    }
}
