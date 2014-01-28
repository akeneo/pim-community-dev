<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\ORM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\TransformBundle\Converter\MetricConverter;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Repository\ProductRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\BatchBundle\Entity\StepExecution;

class ProductReaderSpec extends ObjectBehavior
{
    function let(
        ProductManager $productManager,
        ProductRepository $repository,
        ChannelManager $channelManager,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        StepExecution $stepExecution
    ) {
        $productManager->getFlexibleRepository()->willReturn($repository);

        $this->beConstructedWith($productManager, $channelManager, $completenessManager, $metricConverter);

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
        Channel $channel,
        QueryBuilder $queryBuilder,
        AbstractQuery $query,
        ProductInterface $sku1,
        ProductInterface $sku2
    ) {
        $channelManager->getChannels(array('code' => 'foobar'))->willReturn(array($channel));
        $repository->buildByChannelAndCompleteness($channel)->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->execute()->willReturn(array($sku1, $sku2));

        $this->setChannel('foobar');
        $this->read()->shouldReturn($sku1);
        $this->read()->shouldReturn($sku2);
        $this->read()->shouldReturn(null);
    }

    function it_generates_channel_completenesses_first_time_it_reads(
        $channelManager,
        $completenessManager,
        $repository,
        Channel $channel,
        QueryBuilder $queryBuilder,
        AbstractQuery $query,
        ProductInterface $sku1,
        ProductInterface $sku2
    ) {
        $channelManager->getChannels(array('code' => 'foobar'))->willReturn(array($channel));
        $repository->buildByChannelAndCompleteness($channel)->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->execute()->willReturn(array($sku1, $sku2));

        $completenessManager->generateChannelCompletenesses($channel)->shouldBeCalledTimes(1);

        $this->setChannel('foobar');
        $this->read();
        $this->read();
        $this->read();
    }

    function it_converts_metric_values(
        $channelManager,
        $repository,
        $metricConverter,
        Channel $channel,
        QueryBuilder $queryBuilder,
        AbstractQuery $query,
        ProductInterface $sku1,
        ProductInterface $sku2
    ) {
        $channelManager->getChannels(array('code' => 'foobar'))->willReturn(array($channel));
        $repository->buildByChannelAndCompleteness($channel)->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->execute()->willReturn(array($sku1, $sku2));

        $metricConverter->convert($sku1, $channel)->shouldBeCalled();
        $metricConverter->convert($sku2, $channel)->shouldBeCalled();

        $this->setChannel('foobar');
        $this->read();
        $this->read();
        $this->read();
    }

    function it_increments_read_count_each_time_it_reads(
        $channelManager,
        $repository,
        $stepExecution,
        Channel $channel,
        QueryBuilder $queryBuilder,
        AbstractQuery $query,
        ProductInterface $sku1,
        ProductInterface $sku2
    ) {
        $channelManager->getChannels(array('code' => 'foobar'))->willReturn(array($channel));
        $repository->buildByChannelAndCompleteness($channel)->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->execute()->willReturn(array($sku1, $sku2));

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(2);

        $this->setChannel('foobar');
        $this->read();
        $this->read();
        $this->read();
    }

    function its_read_method_throws_exception_if_channel_cannot_be_found($channelManager)
    {
        $channelManager->getChannels(array('code' => 'mobile'))->willReturn(array());

        $this->setChannel('mobile');
        $this->shouldThrow(new \InvalidArgumentException('Could not find the channel "mobile"'))->duringRead();
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
                        'label'    => 'pim_import_export.export.channel.label',
                        'help'     => 'pim_import_export.export.channel.help'
                    )
                )
            )
        );
    }
}
