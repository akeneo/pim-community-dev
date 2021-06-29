<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit\ProductAndProductModelReader;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Promise\ReturnPromise;

class ProductAndProductModelReaderSpec extends ObjectBehavior
{
    function it_is_initializable(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            true
        );

        $this->shouldHaveType(ProductAndProductModelReader::class);
    }

    function it_sets_step_execution(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            true
        );

        $this->setStepExecution($stepExecution)->shouldReturn(null);
    }

    function it_reads_products_and_product_models(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        StepExecution $stepExecution,
        ChannelInterface $channel,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductModelInterface $productModel3,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            false
        );

        $this->setStepExecution($stepExecution);
        $filters = [
            'data' => [
                [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
                [
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => [
                        'camcorder',
                    ],
                ],
            ],
            'structure' => [
                'scope' => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ],
        ];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCode()->willReturn('mobile');

        $pqbFactory->create(['filters' => $filters['data'], 'default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);
        $pqb->execute()
            ->shouldBeCalled()
            ->willReturn($cursor);

        $products = [$productModel1, $product1, $productModel2, $product2, $product3, $productModel3];
        $productsCount = count($products);
        $cursor->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $cursor->current()->will(new ReturnPromise($products));
        $cursor->next()->shouldBeCalledTimes(5);

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(6);

        $productModel1->getCode()->willReturn('product_model_1');
        $productModel2->getCode()->willReturn('product_model_2');
        $productModel3->getCode()->willReturn('product_model_3');

        $this->initialize();
        $this->read()->shouldReturn($productModel1);
        $this->read()->shouldReturn($product1);
        $this->read()->shouldReturn($productModel2);
        $this->read()->shouldReturn($product2);
        $this->read()->shouldReturn($product3);
        $this->read()->shouldReturn($productModel3);
        $this->read()->shouldReturn(null);
    }

    function it_reads_products_and_product_models_with_read_children(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        StepExecution $stepExecution,
        ChannelInterface $channel,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductModelInterface $productModel3,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            true
        );

        $this->setStepExecution($stepExecution);

        $filters = [
            'data' => [
                [
                    'field' => 'id',
                    'operator' => 'NOT IN',
                    'value' => [1, 2, 3, 4, 42],
                ],
                [
                    'field' => 'label_or_identifier',
                    'operator' => 'CONTAINS',
                    'value' => 'something',
                ],
            ],
            'structure' => [
                'scope' => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ],
        ];
        $readChildrenFiltersData = [
            [
                'field' => 'self_and_ancestor.id',
                'operator' => 'NOT IN',
                'value' => [1, 2, 3, 4, 42],
            ],
            [
                'field' => 'self_and_ancestor.label_or_identifier',
                'operator' => 'CONTAINS',
                'value' => 'something',
            ],
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCode()->willReturn('mobile');

        $pqbFactory->create(['filters' => $readChildrenFiltersData, 'default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);
        $pqb->execute()
            ->shouldBeCalled()
            ->willReturn($cursor);

        $products = [$productModel1, $product1, $productModel2, $product2, $product3, $productModel3];
        $productsCount = count($products);
        $cursor->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $cursor->current()->will(new ReturnPromise($products));
        $cursor->next()->shouldBeCalledTimes(5);

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(6);

        $productModel1->getCode()->willReturn('product_model_1');
        $productModel2->getCode()->willReturn('product_model_2');
        $productModel3->getCode()->willReturn('product_model_3');

        $this->initialize();
        $this->read()->shouldReturn($productModel1);
        $this->read()->shouldReturn($product1);
        $this->read()->shouldReturn($productModel2);
        $this->read()->shouldReturn($product2);
        $this->read()->shouldReturn($product3);
        $this->read()->shouldReturn($productModel3);
        $this->read()->shouldReturn(null);
    }

    function it_returns_the_total_items_the_reader_is_going_to_read(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        StepExecution $stepExecution,
        ChannelInterface $channel,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            false
        );

        $this->setStepExecution($stepExecution);
        $filters = [
            'data' => [
                [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
                [
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => [
                        'camcorder',
                    ],
                ],
            ],
            'structure' => [
                'scope' => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ],
        ];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCode()->willReturn('mobile');

        $pqbFactory->create(['filters' => $filters['data'], 'default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);
        $pqb->execute()
            ->shouldBeCalled()
            ->willReturn($cursor);

        $expectedTotalItems = 5;
        $cursor->count()->willReturn($expectedTotalItems);

        $this->initialize();
        $this->totalItems()->shouldReturn($expectedTotalItems);
    }

    function it_throws_if_the_reader_is_not_initialized(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            false
        );

        $this->shouldThrow(\RuntimeException::class)
            ->during('totalItems');
    }
}
