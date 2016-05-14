<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\Doctrine;

use Akeneo\Component\Batch\Job\JobParameters;
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

    function it_reads_products_one_by_one(
        $channelRepository,
        $repository,
        $stepExecution,
        ChannelInterface $channel,
        Builder $builder,
        AbstractQuery $query,
        ProductInterface $sku1,
        Cursor $cursor,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('channel')->willReturn('foobar');

        $channelRepository->findOneByIdentifier('foobar')->willReturn($channel);
        $repository->buildByChannelAndCompleteness($channel)->willReturn($builder);

        $builder->getQuery()->willReturn($query);
        $query->execute()->willReturn($cursor);

        $cursor->getNext()->willReturn(null);
        $cursor->current()->willReturn($sku1);
        $cursor->next()->willReturn(null);

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(1);

        $this->read()->shouldReturn($sku1);
    }

    function it_generates_channel_completenesses_first_time_it_reads(
        $channelRepository,
        $completenessManager,
        $repository,
        $stepExecution,
        ChannelInterface $channel,
        Builder $builder,
        AbstractQuery $query,
        ProductInterface $sku1,
        Cursor $cursor,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('channel')->willReturn('foobar');

        $channelRepository->findOneByIdentifier('foobar')->willReturn($channel);
        $repository->buildByChannelAndCompleteness($channel)->willReturn($builder);

        $builder->getQuery()->willReturn($query);
        $query->execute()->willReturn($cursor);

        $cursor->getNext()->willReturn(null);
        $cursor->current()->willReturn($sku1);
        $cursor->next()->willReturn(null);

        $completenessManager->generateMissingForChannel($channel)->shouldBeCalledTimes(1);

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(1);

        $this->read()->shouldReturn($sku1);
    }

    function it_converts_metric_values(
        $stepExecution,
        $channelRepository,
        $repository,
        $converter,
        ChannelInterface $channel,
        Builder $builder,
        AbstractQuery $query,
        ProductInterface $sku1,
        Cursor $cursor,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('channel')->willReturn('foobar');

        $channelRepository->findOneByIdentifier('foobar')->willReturn($channel);
        $repository->buildByChannelAndCompleteness($channel)->willReturn($builder);

        $builder->getQuery()->willReturn($query);
        $query->execute()->willReturn($cursor);

        $cursor->getNext()->willReturn(null);
        $cursor->current()->willReturn($sku1);
        $cursor->next()->willReturn(null);

        $converter->convert($sku1, $channel)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(1);

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
        Cursor $cursor,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('channel')->willReturn('foobar');

        $channelRepository->findOneByIdentifier('foobar')->willReturn($channel);
        $repository->buildByChannelAndCompleteness($channel)->willReturn($builder);

        $builder->getQuery()->willReturn($query);
        $query->execute()->willReturn($cursor);

        $cursor->getNext()->willReturn(null);
        $cursor->current()->willReturn($sku1);
        $cursor->next()->willReturn(null);

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(1);

        $this->read();
    }
}
