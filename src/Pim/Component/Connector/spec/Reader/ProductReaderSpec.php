<?php

namespace spec\Pim\Component\Connector\Reader;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Converter\MetricConverter;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\CategoryInterface;
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

    function it_is_configurable()
    {
        $this->getChannel()->shouldReturn(null);
        $this->setChannel('mobile');
        $this->getChannel()->shouldReturn('mobile');
    }

    function it_reads_enabled_products(
        $pqbFactory,
        $channelRepository,
        $metricConverter,
        $objectDetacher,
        $stepExecution,
        ChannelInterface $channel,
        CategoryInterface $channelRoot,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3
    ) {
        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCategory()->willReturn($channelRoot);
        $channelRoot->getId()->willReturn(42);
        $channel->getCode()->willReturn('mobile');

        $pqbFactory->create(['default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);
        $pqb->addFilter('enabled', '=', true, [])->shouldBeCalled();
        $pqb->addFilter('completeness', '=', 100, [])->shouldBeCalled();
        $pqb->addFilter('categories.id', 'IN CHILDREN', [42], [])->shouldBeCalled();
        $pqb->execute()
            ->shouldBeCalled()
            ->willReturn($cursor);

        $products = [$product1, $product2, $product3];
        $productsCount = count($products);
        $cursor->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $cursor->next()->shouldBeCalled();
        $cursor->current()->will(new ReturnPromise($products));

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(3);
        $objectDetacher->detach(Argument::any())->shouldBeCalledTimes(3);
        $metricConverter->convert(Argument::any(), $channel)->shouldBeCalledTimes(3);

        $this->setChannel('mobile');
        $this->initialize();
        $this->read()->shouldReturn($product1);
        $this->read()->shouldReturn($product2);
        $this->read()->shouldReturn($product3);
        $this->read()->shouldReturn(null);
    }

    function it_reads_disabled_products(
        $pqbFactory,
        $channelRepository,
        $metricConverter,
        $objectDetacher,
        $stepExecution,
        ChannelInterface $channel,
        CategoryInterface $channelRoot,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3
    ) {
        $this->setEnabled('disabled');

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCategory()->willReturn($channelRoot);
        $channelRoot->getId()->willReturn(42);
        $channel->getCode()->willReturn('mobile');

        $pqbFactory->create(['default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);
        $pqb->addFilter('enabled', '=', false, [])->shouldBeCalled();
        $pqb->addFilter('completeness', '=', 100, [])->shouldBeCalled();
        $pqb->addFilter('categories.id', 'IN CHILDREN', [42], [])->shouldBeCalled();
        $pqb->execute()
            ->shouldBeCalled()
            ->willReturn($cursor);

        $products = [$product1, $product2, $product3];
        $productsCount = count($products);
        $cursor->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $cursor->next()->shouldBeCalled();
        $cursor->current()->will(new ReturnPromise($products));

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(3);
        $objectDetacher->detach(Argument::any())->shouldBeCalledTimes(3);
        $metricConverter->convert(Argument::any(), $channel)->shouldBeCalledTimes(3);

        $this->setChannel('mobile');
        $this->initialize();
        $this->read()->shouldReturn($product1);
        $this->read()->shouldReturn($product2);
        $this->read()->shouldReturn($product3);
        $this->read()->shouldReturn(null);
    }

    function it_reads_all_products(
        $pqbFactory,
        $channelRepository,
        $metricConverter,
        $objectDetacher,
        $stepExecution,
        ChannelInterface $channel,
        CategoryInterface $channelRoot,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3
    ) {
        $this->setEnabled('all');

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCategory()->willReturn($channelRoot);
        $channelRoot->getId()->willReturn(42);
        $channel->getCode()->willReturn('mobile');

        $pqbFactory->create(['default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);
        $pqb->addFilter('enabled', Argument::cetera())->shouldNotBeCalled();
        $pqb->addFilter('completeness', '=', 100, [])->shouldBeCalled();
        $pqb->addFilter('categories.id', 'IN CHILDREN', [42], [])->shouldBeCalled();
        $pqb->execute()
            ->shouldBeCalled()
            ->willReturn($cursor);

        $products = [$product1, $product2, $product3];
        $productsCount = count($products);
        $cursor->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $cursor->next()->shouldBeCalled();
        $cursor->current()->will(new ReturnPromise($products));

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(3);
        $objectDetacher->detach(Argument::any())->shouldBeCalledTimes(3);
        $metricConverter->convert(Argument::any(), $channel)->shouldBeCalledTimes(3);

        $this->setChannel('mobile');
        $this->initialize();
        $this->read()->shouldReturn($product1);
        $this->read()->shouldReturn($product2);
        $this->read()->shouldReturn($product3);
        $this->read()->shouldReturn(null);
    }

    function it_generates_the_completeness_on_initialization(
        $pqbFactory,
        $channelRepository,
        $completenessManager,
        ChannelInterface $channel,
        CategoryInterface $channelRoot,
        ProductQueryBuilderInterface $pqb
    ) {
        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCategory()->willReturn($channelRoot);
        $channel->getCode()->willReturn('mobile');
        $pqbFactory->create(['default_scope' => 'mobile'])->willReturn($pqb);

        $completenessManager->generateMissingForChannel($channel)->shouldBeCalledTimes(1);

        $this->setChannel('mobile');
        $this->initialize();
    }

    function it_exposes_the_channel_and_status_fields($channelRepository)
    {
        $channelRepository->getLabelsIndexedByCode()->willReturn(
            [
                'foo' => 'Foo',
                'bar' => 'Bar',
            ]
        );

        $this->getConfigurationFields()->shouldReturn(
            [
                'channel' => [
                    'type'    => 'choice',
                    'options' => [
                        'choices'  => [
                            'foo' => 'Foo',
                            'bar' => 'Bar',
                        ],
                        'required' => true,
                        'select2'  => true,
                        'label'    => 'pim_connector.export.channel.label',
                        'help'     => 'pim_connector.export.channel.help'
                    ]
                ],
                'enabled' => [
                    'type'    => 'choice',
                    'options' => [
                        'choices'  => [
                            'enabled' => 'pim_connector.export.status.choice.enabled',
                            'disabled' => 'pim_connector.export.status.choice.disabled',
                            'all' => 'pim_connector.export.status.choice.all'
                        ],
                        'required' => true,
                        'select2'  => true,
                        'label'    => 'pim_connector.export.status.label',
                        'help'     => 'pim_connector.export.status.help',
                    ]
                ],
            ]
        );
    }
}
