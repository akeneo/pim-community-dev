<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TransformBundle\Converter\MetricConverter;

class ORMProductReaderSpec extends ObjectBehavior
{
    function let(
        ProductRepository $repository,
        ChannelManager $channelManager,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        StepExecution $stepExecution,
        EntityManager $entityManager
    ) {
        $this->beConstructedWith($repository, $channelManager, $completenessManager, $metricConverter, $entityManager);

        $this->setStepExecution($stepExecution);
    }

    function it_is_configurable(AbstractQuery $query)
    {
        $this->getChannel()->shouldReturn(null);
        $this->getQuery()->shouldReturn(null);

        $this->setChannel('mobile');
        $this->setQuery($query);

        $this->getChannel()->shouldReturn('mobile');
        $this->getQuery()->shouldReturn($query);
    }

    function it_reads_products_one_by_one(
        $channelManager,
        $repository,
        ChannelInterface $channel,
        From $from,
        QueryBuilder $queryBuilder,
        AbstractQuery $query,
        ProductInterface $sku1,
        ProductInterface $sku2,
        ProductInterface $sku3
    ) {
        $channelManager->getChannelByCode('foobar')->willReturn($channel);
        $repository->buildByChannelAndCompleteness($channel)->willReturn($queryBuilder);
        $queryBuilder->getRootAliases()->willReturn(['root']);
        $queryBuilder->getDQLPart('from')->willReturn([$from]);
        $queryBuilder->select('root.id')->willReturn($queryBuilder);
        $queryBuilder->resetDQLPart('from')->willReturn($queryBuilder);
        $from->getFrom()->willReturn('from_table');
        $from->getAlias()->willReturn('alias_table');
        $queryBuilder->from('from_table', 'alias_table', 'root.id')->willReturn($queryBuilder);
        $queryBuilder->groupBy('root.id')->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);

        $query->getArrayResult()->willReturn(array_flip([1, 33, 789]));

        $repository->findByIds([1, 33, 789])->willReturn([$sku1, $sku2, $sku3]);

        $this->setChannel('foobar');
        $this->read()->shouldReturn($sku1);
    }

    function it_generates_channel_completenesses_first_time_it_reads(
        $channelManager,
        $completenessManager,
        $repository,
        From $from,
        ChannelInterface $channel,
        QueryBuilder $queryBuilder,
        AbstractQuery $query,
        ProductInterface $sku1,
        ProductInterface $sku2,
        ProductInterface $sku3
    ) {
        $channelManager->getChannelByCode('foobar')->willReturn($channel);
        $repository->buildByChannelAndCompleteness($channel)->willReturn($queryBuilder);
        $queryBuilder->getRootAliases()->willReturn(['root']);
        $queryBuilder->getDQLPart('from')->willReturn([$from]);
        $queryBuilder->select('root.id')->willReturn($queryBuilder);
        $queryBuilder->resetDQLPart('from')->willReturn($queryBuilder);
        $from->getFrom()->willReturn('from_table');
        $from->getAlias()->willReturn('alias_table');
        $queryBuilder->from('from_table', 'alias_table', 'root.id')->willReturn($queryBuilder);
        $queryBuilder->groupBy('root.id')->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);

        $query->getArrayResult()->willReturn(array_flip([1, 33, 789]));

        $repository->findByIds([1, 33, 789])->willReturn([$sku1, $sku2, $sku3]);

        $completenessManager->generateMissingForChannel($channel)->shouldBeCalledTimes(1);

        $this->setChannel('foobar');
        $this->read()->shouldReturn($sku1);
    }

    function it_converts_metric_values(
        $channelManager,
        $repository,
        $metricConverter,
        ChannelInterface $channel,
        From $from,
        QueryBuilder $queryBuilder,
        AbstractQuery $query,
        ProductInterface $sku1,
        ProductInterface $sku2,
        ProductInterface $sku3
    ) {
        $channelManager->getChannelByCode('foobar')->willReturn($channel);
        $repository->buildByChannelAndCompleteness($channel)->willReturn($queryBuilder);
        $queryBuilder->getRootAliases()->willReturn(['root']);
        $queryBuilder->getDQLPart('from')->willReturn([$from]);
        $queryBuilder->select('root.id')->willReturn($queryBuilder);
        $queryBuilder->resetDQLPart('from')->willReturn($queryBuilder);
        $from->getFrom()->willReturn('from_table');
        $from->getAlias()->willReturn('alias_table');
        $queryBuilder->from('from_table', 'alias_table', 'root.id')->willReturn($queryBuilder);
        $queryBuilder->groupBy('root.id')->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);

        $query->getArrayResult()->willReturn(array_flip([1, 33, 789]));

        $repository->findByIds([1, 33, 789])->willReturn([$sku1, $sku2, $sku3]);
        $this->setChannel('foobar');

        $metricConverter->convert($sku1, $channel)->shouldBeCalled();

        $this->read()->shouldReturn($sku1);
    }

    function it_increments_read_count_each_time_it_reads(
        $channelManager,
        $repository,
        $stepExecution,
        $channelManager,
        $repository,
        ChannelInterface $channel,
        From $from,
        QueryBuilder $queryBuilder,
        AbstractQuery $query,
        ProductInterface $sku1,
        ProductInterface $sku2,
        ProductInterface $sku3
    ) {
        $channelManager->getChannelByCode('foobar')->willReturn($channel);
        $repository->buildByChannelAndCompleteness($channel)->willReturn($queryBuilder);
        $queryBuilder->getRootAliases()->willReturn(['root']);
        $queryBuilder->getDQLPart('from')->willReturn([$from]);
        $queryBuilder->select('root.id')->willReturn($queryBuilder);
        $queryBuilder->resetDQLPart('from')->willReturn($queryBuilder);
        $from->getFrom()->willReturn('from_table');
        $from->getAlias()->willReturn('alias_table');
        $queryBuilder->from('from_table', 'alias_table', 'root.id')->willReturn($queryBuilder);
        $queryBuilder->groupBy('root.id')->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);

        $query->getArrayResult()->willReturn(array_flip([1, 33, 789]));

        $repository->findByIds([1, 33, 789])->willReturn([$sku1, $sku2, $sku3]);
        $this->setChannel('foobar');

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(3);

        $this->setChannel('foobar');
        $this->read();
        $this->read();
        $this->read();
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
