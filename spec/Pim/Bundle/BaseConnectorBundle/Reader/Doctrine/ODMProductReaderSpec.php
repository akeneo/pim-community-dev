<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ORM\AbstractQuery;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\TransformBundle\Converter\MetricConverter;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class ODMProductReaderSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $repository,
        ChannelManager $channelManager,
        CompletenessManager $completenessManager,
        MetricConverter $converter,
        StepExecution $stepExecution,
        DocumentManager $documentManager
    ) {
        $this->beConstructedWith($repository, $channelManager, $completenessManager, $converter, $documentManager);

        $this->setStepExecution($stepExecution);
    }

    function it_has_a_channel()
    {
        $this->setChannel('mobile');
        $this->getChannel()->shouldReturn('mobile');
    }

    function it_reads_products_one_by_one(
        $channelManager,
        $repository,
        ChannelInterface $channel,
        Builder $builder,
        AbstractQuery $query,
        ProductInterface $sku1,
        Cursor $cursor
    ) {
        $channelManager->getChannelByCode('foobar')->willReturn($channel);
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
        $channelManager,
        $completenessManager,
        $repository,
        ChannelInterface $channel,
        Builder $builder,
        AbstractQuery $query,
        ProductInterface $sku1,
        Cursor $cursor
    ) {
        $channelManager->getChannelByCode('foobar')->willReturn($channel);
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
        $channelManager,
        $repository,
        $converter,
        ChannelInterface $channel,
        Builder $builder,
        AbstractQuery $query,
        ProductInterface $sku1,
        Cursor $cursor
    ) {
        $channelManager->getChannelByCode('foobar')->willReturn($channel);
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
        $channelManager,
        $repository,
        $stepExecution,
        ChannelInterface $channel,
        Builder $builder,
        AbstractQuery $query,
        ProductInterface $sku1,
        Cursor $cursor
    ) {
        $channelManager->getChannelByCode('foobar')->willReturn($channel);
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

    function it_exposes_the_channel_field($channelManager)
    {
        $channelManager->getChannelChoices()->willReturn(
            array(
                'foo' => 'Foo',
                'bar' => 'Bar',
            )
        );

        $this->getConfigurationFields()->shouldReturn(
            array(
                'channel' => array(
                    'type'    => 'choice',
                    'options' => array(
                        'choices'  => array(
                            'foo' => 'Foo',
                            'bar' => 'Bar',
                        ),
                        'required' => true,
                        'select2'  => true,
                        'label'    => 'pim_base_connector.export.channel.label',
                        'help'     => 'pim_base_connector.export.channel.help'
                    )
                )
            )
        );
    }
}
