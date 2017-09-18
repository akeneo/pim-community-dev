<?php

namespace spec\Pim\Component\Connector\Reader\Database;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Converter\MetricConverter;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Prophecy\Argument;
use Prophecy\Promise\ReturnPromise;

class ProductReaderSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        ObjectDetacherInterface $objectDetacher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            $completenessManager,
            $metricConverter,
            $objectDetacher,
            true
        );

        $this->setStepExecution($stepExecution);
    }

    function it_is_a_database_product_reader()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemReaderInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Item\InitializableInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
        $this->shouldImplement('Pim\Component\Connector\Reader\Database\DatabaseProductReaderInterface');
    }

    function it_reads_products(
        $pqbFactory,
        $channelRepository,
        $metricConverter,
        $stepExecution,
        $completenessManager,
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
                ]
            ],
            'structure' => [
                'scope'   => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ]
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

        $completenessManager->generateMissingForChannel($channel)->shouldBeCalled();
        $products = [$product1, $product2, $product3];
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

        $this->initialize();
        $this->read()->shouldReturn($product1);
        $this->read()->shouldReturn($product2);
        $this->read()->shouldReturn($product3);
        $this->read()->shouldReturn(null);
    }

    function it_checks_that_the_product_is_valid(
        $pqbFactory,
        $channelRepository,
        $stepExecution,
        $completenessManager,
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
                ]
            ],
            'structure' => [
                'scope'   => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ]
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

        $completenessManager->generateMissingForChannel($channel)->shouldBeCalled();
        $products = [$product1, $product2, $product3];
        $productsCount = count($products);
        $cursor->valid()
            ->shouldBeCalled()
            ->will(
                function () use (&$productsCount) {
                    return $productsCount-- > 0;
                }
            );

        $this->initialize();
        $this->isProductValid()->shouldReturn(true);
    }

    function it_checks_that_the_product_is_not_valid(
        $pqbFactory,
        $channelRepository,
        $stepExecution,
        $completenessManager,
        ChannelInterface $channel,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
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
                ]
            ],
            'structure' => [
                'scope'   => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ]
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

        $completenessManager->generateMissingForChannel($channel)->shouldBeCalled();
        $products = [];
        $productsCount = count($products);
        $cursor->valid()
            ->shouldBeCalled()
            ->will(
                function () use (&$productsCount) {
                    return $productsCount-- > 0;
                }
            );

        $this->initialize();
        $this->isProductValid()->shouldReturn(false);
    }

    function it_goes_to_the_next_product(
        $pqbFactory,
        $channelRepository,
        $stepExecution,
        $completenessManager,
        ChannelInterface $channel,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
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
                ]
            ],
            'structure' => [
                'scope'   => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ]
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

        $completenessManager->generateMissingForChannel($channel)->shouldBeCalled();
        $cursor->next()->shouldBeCalled();

        $this->initialize();
        $this->nextProduct();
    }

    function it_returns_the_current_product(
        $pqbFactory,
        $channelRepository,
        $stepExecution,
        $completenessManager,
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
                ]
            ],
            'structure' => [
                'scope'   => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ]
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

        $completenessManager->generateMissingForChannel($channel)->shouldBeCalled();
        $products = [$product1, $product2, $product3];
        $cursor->current()
            ->shouldBeCalled()
            ->will(new ReturnPromise($products));

        $this->initialize();
        $this->getCurrentProduct()->shouldReturn($product1);
    }
}
