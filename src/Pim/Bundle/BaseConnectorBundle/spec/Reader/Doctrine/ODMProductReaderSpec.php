<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\Doctrine;

use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ORM\AbstractQuery;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Converter\MetricConverter;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class ODMProductReaderSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $repository,
        ChannelRepositoryInterface $channelRepository,
        CompletenessManager $completenessManager,
        MetricConverter $converter,
        StepExecution $stepExecution,
        DocumentManager $documentManager
    ) {
        $this->beConstructedWith($repository, $channelRepository, $completenessManager, $converter, $documentManager);

        $this->setStepExecution($stepExecution);
    }

    function it_has_a_channel()
    {
        $this->setChannel('mobile');
        $this->getChannel()->shouldReturn('mobile');
    }

    function it_reads_products_one_by_one(
        $channelRepository,
        $repository,
        ChannelInterface $channel,
        Builder $builder,
        AbstractQuery $query,
        ProductInterface $sku1,
        Cursor $cursor
    ) {
        $channelRepository->findOneByIdentifier('foobar')->willReturn($channel);
        $repository->buildByChannelAndCompleteness($channel)->willReturn($builder);

        $builder->getQuery()->willReturn($query);
        $query->execute()->willReturn($cursor);

        $cursor->getNext()->willReturn(null);
        $cursor->current()->willReturn($sku1);
        $cursor->next()->willReturn(null);

        $this->setChannel('foobar');
        $this->read()->shouldReturn($sku1);
    }

    function it_generates_channel_completenesses_first_time_it_reads(
        $channelRepository,
        $completenessManager,
        $repository,
        ChannelInterface $channel,
        Builder $builder,
        AbstractQuery $query,
        ProductInterface $sku1,
        Cursor $cursor
    ) {
        $channelRepository->findOneByIdentifier('foobar')->willReturn($channel);
        $repository->buildByChannelAndCompleteness($channel)->willReturn($builder);

        $builder->getQuery()->willReturn($query);
        $query->execute()->willReturn($cursor);

        $cursor->getNext()->willReturn(null);
        $cursor->current()->willReturn($sku1);
        $cursor->next()->willReturn(null);

        $completenessManager->generateMissingForChannel($channel)->shouldBeCalledTimes(1);

        $this->setChannel('foobar');
        $this->read()->shouldReturn($sku1);
    }

    function it_converts_metric_values(
        $channelRepository,
        $repository,
        $converter,
        ChannelInterface $channel,
        Builder $builder,
        AbstractQuery $query,
        ProductInterface $sku1,
        Cursor $cursor
    ) {
        $channelRepository->findOneByIdentifier('foobar')->willReturn($channel);
        $repository->buildByChannelAndCompleteness($channel)->willReturn($builder);

        $builder->getQuery()->willReturn($query);
        $query->execute()->willReturn($cursor);

        $cursor->getNext()->willReturn(null);
        $cursor->current()->willReturn($sku1);
        $cursor->next()->willReturn(null);
        $this->setChannel('foobar');

        $converter->convert($sku1, $channel)->shouldBeCalled();

        $this->read()->shouldReturn($sku1);
    }

    function it_increments_read_count_each_time_it_reads(
        $channelRepository,
        $repository,
        $stepExecution,
        ChannelInterface $channel,
        Builder $builder,
        AbstractQuery $query,
        ProductInterface $sku1,
        Cursor $cursor
    ) {
        $channelRepository->findOneByIdentifier('foobar')->willReturn($channel);
        $repository->buildByChannelAndCompleteness($channel)->willReturn($builder);

        $builder->getQuery()->willReturn($query);
        $query->execute()->willReturn($cursor);

        $cursor->getNext()->willReturn(null);
        $cursor->current()->willReturn($sku1);
        $cursor->next()->willReturn(null);
        $this->setChannel('foobar');

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(1);

        $this->setChannel('foobar');
        $this->read();
    }

    function it_exposes_the_channel_field($channelRepository)
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
                        'label'    => 'pim_base_connector.export.channel.label',
                        'help'     => 'pim_base_connector.export.channel.help'
                    ]
                ]
            ]
        );
    }
}
