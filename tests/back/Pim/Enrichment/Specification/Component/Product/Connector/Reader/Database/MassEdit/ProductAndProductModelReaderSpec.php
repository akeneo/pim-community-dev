<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit\ProductAndProductModelReader;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;

class ProductAndProductModelReaderSpec extends ObjectBehavior
{
    function it_is_initializable(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            $productRepository,
            $productModelRepository,
            false
        );

        $this->shouldHaveType(ProductAndProductModelReader::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    function it_reads_products_and_product_models(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ChannelInterface $channel,
        ProductQueryBuilderInterface $pqb,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductInterface $product1,
        ProductInterface $product2,
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            $productRepository,
            $productModelRepository,
            false
        );
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
        $jobParameters->get('filters')->willReturn($filters);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $this->setStepExecution($stepExecution);

        $channel->getCode()->willReturn('mobile');
        $channelRepository->findOneByIdentifier('mobile')->shouldBeCalled()->willReturn($channel);

        $pqbFactory->create(['filters' => $filters['data'], 'default_scope' => 'mobile'])
            ->shouldBeCalled()->willReturn($pqb);

        $results = [
            new IdentifierResult('pm_1', ProductModelInterface::class, 'product_model_42'),
            new IdentifierResult('product_first', ProductInterface::class, 'product_e95ac0d2-a746-4d1a-a3c0-56c7bf48de7d'),
            new IdentifierResult('pm_2', ProductModelInterface::class, 'product_model_100'),
            new IdentifierResult('pm_deleted', ProductModelInterface::class, 'product_model_404'),
            new IdentifierResult('product_deleted', ProductInterface::class, 'product_220c60fb-936f-4dd5-9fc9-99a032c529a9'),
            new IdentifierResult(null, ProductInterface::class, 'product_b918a5b8-0d0e-44df-b14a-45c88a5350c9'),
        ];
        $cursor = new class extends \ArrayIterator implements CursorInterface{};
        $pqb->execute()->shouldBeCalled()->willReturn(new $cursor($results));

        $productModelRepository->findOneByIdentifier('pm_1')->shouldBeCalled()->willReturn($productModel1);
        $productRepository->findOneBy(['uuid' => 'e95ac0d2-a746-4d1a-a3c0-56c7bf48de7d'])->shouldBeCalled()->willReturn($product1);
        $productModelRepository->findOneByIdentifier('pm_2')->shouldBeCalled()->willReturn($productModel2);
        $productModelRepository->findOneByIdentifier('pm_deleted')->shouldBeCalled()->willReturn(null);
        $productRepository->findOneBy(['uuid' => '220c60fb-936f-4dd5-9fc9-99a032c529a9'])->shouldBeCalled()->willReturn(null);
        $productRepository->findOneBy(['uuid' => 'b918a5b8-0d0e-44df-b14a-45c88a5350c9'])->shouldBeCalled()->willReturn($product2);

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(4);

        $this->initialize();
        $this->read()->shouldReturn($productModel1);
        $this->read()->shouldReturn($product1);
        $this->read()->shouldReturn($productModel2);
        $this->read()->shouldReturn($product2);
        $this->read()->shouldReturn(null);
    }

    function it_reads_products_and_product_models_with_their_children(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
        ProductQueryBuilderInterface $pqb,
        StepExecution $stepExecution,
        ChannelInterface $channel,
        JobParameters $jobParameters,
        CursorInterface $cursor,
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            $productRepository,
            $productModelRepository,
            true
        );
        $jobParameters->get('filters')->willReturn(
            [
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
            ]
        );
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $this->setStepExecution($stepExecution);

        $channel->getCode()->willReturn('mobile');
        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);

        $pqbFactory->create(
            [
                'filters' => [
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
                ],
                'default_scope' => 'mobile',
            ]
        )->shouldBeCalled()->willReturn($pqb);

        $pqb->execute()->shouldBeCalled()->willReturn($cursor);
        $cursor->rewind()->shouldBeCalled();

        $this->initialize();
    }

    function it_returns_the_total_items_the_reader_is_going_to_read(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
        StepExecution $stepExecution,
        ChannelInterface $channel,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            $productRepository,
            $productModelRepository,
            false
        );
        $stepExecution->getJobParameters()->willReturn($jobParameters);
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
            ->shouldBeCalled()->willReturn($pqb);
        $pqb->execute()
            ->shouldBeCalled()
            ->willReturn($cursor);

        $cursor->rewind()->shouldBeCalled();
        $cursor->count()->shouldBeCalled()->willReturn(5);

        $this->initialize();
        $this->totalItems()->shouldReturn(5);
    }

    function it_throws_if_the_reader_is_not_initialized(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            $productRepository,
            $productModelRepository,
            false
        );
        $jobParameters->get('filters')->willReturn([]);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $this->setStepExecution($stepExecution);

        $this->shouldThrow(\RuntimeException::class)
            ->during('totalItems');
    }
}
