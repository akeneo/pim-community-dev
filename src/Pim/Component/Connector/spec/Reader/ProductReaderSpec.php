<?php

namespace spec\Pim\Component\Connector\Reader;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
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
        JobRepositoryInterface $jobRepository,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelRepository,
            $completenessManager,
            $metricConverter,
            $objectDetacher,
            $jobRepository,
            true
        );

        $this->setStepExecution($stepExecution);
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
        ProductInterface $product3,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('channel')->willReturn('mobile');
        $jobParameters->get('enabled')->willReturn('enabled');
        $jobParameters->get('updated')->willReturn('all');
        $jobParameters->get('families')->willReturn('');
        $jobParameters->get('completeness')->willReturn('all');

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCategory()->willReturn($channelRoot);
        $channelRoot->getId()->willReturn(42);
        $channel->getCode()->willReturn('mobile');

        $pqbFactory->create(['default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);
        $pqb->addFilter('enabled', '=', true, [])->shouldBeCalled();
        $pqb->addFilter('completeness', Argument::cetera())->shouldNotBeCalled();
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

        $this->initialize();
        $this->read()->shouldReturn($product1);
        $this->read()->shouldReturn($product2);
        $this->read()->shouldReturn($product3);
        $this->read()->shouldReturn(null);
    }

    function it_reads_products_by_families(
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
        ProductInterface $product3,
        JobParameters $jobParameters
    )
    {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('channel')->willReturn('mobile');
        $jobParameters->get('enabled')->willReturn('enabled');
        $jobParameters->get('updated')->willReturn('all');
        $jobParameters->get('families')->willReturn('mugs,webcams');
        $jobParameters->get('completeness')->willReturn('all');

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCategory()->willReturn($channelRoot);
        $channelRoot->getId()->willReturn(42);
        $channel->getCode()->willReturn('mobile');

        $pqbFactory->create(['default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);

        $pqb->addFilter('family.code', 'IN', ['mugs', 'webcams'], [])->shouldBeCalled();
        $pqb->addFilter('enabled', '=', true, [])->shouldBeCalled();
        $pqb->addFilter('categories.id', 'IN CHILDREN', [42], [])->shouldBeCalled();
        $pqb->execute()
            ->shouldBeCalled()
            ->willReturn($cursor);

        $products      = [$product1, $product2, $product3];
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

        $this->initialize();
        $this->read()->shouldReturn($product1);
        $this->read()->shouldReturn($product2);
        $this->read()->shouldReturn($product3);
        $this->read()->shouldReturn(null);
    }

    function it_reads_complete_products(
        $pqbFactory,
        $channelRepository,
        $stepExecution,
        ChannelInterface $channel,
        CategoryInterface $channelRoot,
        ProductQueryBuilderInterface $pqb,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('channel')->willReturn('mobile');
        $jobParameters->get('enabled')->willReturn('all');
        $jobParameters->get('updated')->willReturn('all');
        $jobParameters->get('locales')->willReturn(['fr_FR', 'en_US']);
        $jobParameters->get('families')->willReturn('');
        $jobParameters->get('completeness')->willReturn('all_complete');

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCategory()->willReturn($channelRoot);
        $channelRoot->getId()->willReturn(42);
        $channel->getCode()->willReturn('mobile');

        $pqbFactory->create(['default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);

        $pqb->addFilter('completeness', '=', 100, ['locale' => 'fr_FR'])->shouldBeCalled();
        $pqb->addFilter('completeness', '=', 100, ['locale' => 'en_US'])->shouldBeCalled();
        $pqb->addFilter('categories.id', 'IN CHILDREN', [42], [])->shouldBeCalled();
        $pqb->execute()->shouldBeCalled();

        $this->initialize();
    }

    function it_reads_incomplete_products(
        $pqbFactory,
        $channelRepository,
        $stepExecution,
        ChannelInterface $channel,
        CategoryInterface $channelRoot,
        ProductQueryBuilderInterface $pqb,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('channel')->willReturn('mobile');
        $jobParameters->get('enabled')->willReturn('all');
        $jobParameters->get('updated')->willReturn('all');
        $jobParameters->get('locales')->willReturn(['fr_FR', 'en_US']);
        $jobParameters->get('families')->willReturn('');
        $jobParameters->get('completeness')->willReturn('all_incomplete');

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCategory()->willReturn($channelRoot);
        $channelRoot->getId()->willReturn(42);
        $channel->getCode()->willReturn('mobile');

        $pqbFactory->create(['default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);

        $pqb->addFilter('completeness', '<', 100, ['locale' => 'fr_FR'])->shouldBeCalled();
        $pqb->addFilter('completeness', '<', 100, ['locale' => 'en_US'])->shouldBeCalled();
        $pqb->addFilter('categories.id', 'IN CHILDREN', [42], [])->shouldBeCalled();
        $pqb->execute()->shouldBeCalled();

        $this->initialize();
    }

    function it_reads_at_least_one_complete_products(
        $pqbFactory,
        $channelRepository,
        $stepExecution,
        ChannelInterface $channel,
        CategoryInterface $channelRoot,
        ProductQueryBuilderInterface $pqb,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('channel')->willReturn('mobile');
        $jobParameters->get('enabled')->willReturn('all');
        $jobParameters->get('updated')->willReturn('all');
        $jobParameters->get('families')->willReturn('');
        $jobParameters->get('completeness')->willReturn('at_least_one_complete');

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCategory()->willReturn($channelRoot);
        $channelRoot->getId()->willReturn(42);
        $channel->getCode()->willReturn('mobile');

        $pqbFactory->create(['default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);

        $pqb->addFilter('completeness', '=', 100, [])->shouldBeCalled();
        $pqb->addFilter('categories.id', 'IN CHILDREN', [42], [])->shouldBeCalled();
        $pqb->execute()->shouldBeCalled();

        $this->initialize();
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
        ProductInterface $product3,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('channel')->willReturn('mobile');
        $jobParameters->get('enabled')->willReturn('disabled');
        $jobParameters->get('updated')->willReturn('all');
        $jobParameters->get('families')->willReturn('');
        $jobParameters->get('completeness')->willReturn('at_least_one_complete');

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
        ProductInterface $product3,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('channel')->willReturn('mobile');
        $jobParameters->get('enabled')->willReturn('all');
        $jobParameters->get('updated')->willReturn('all');
        $jobParameters->get('families')->willReturn('');
        $jobParameters->get('completeness')->willReturn('at_least_one_complete');

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
        $stepExecution,
        ChannelInterface $channel,
        CategoryInterface $channelRoot,
        ProductQueryBuilderInterface $pqb,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('channel')->willReturn('mobile');
        $jobParameters->get('enabled')->willReturn('enabled');
        $jobParameters->get('updated')->willReturn('all');
        $jobParameters->get('families')->willReturn('');
        $jobParameters->get('completeness')->willReturn('at_least_one_complete');

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCategory()->willReturn($channelRoot);
        $channel->getCode()->willReturn('mobile');
        $pqbFactory->create(['default_scope' => 'mobile'])->willReturn($pqb);

        $completenessManager->generateMissingForChannel($channel)->shouldBeCalledTimes(1);

        $this->initialize();
    }

    function it_reads_only_products_updated_since_last_export(
        $pqbFactory,
        $channelRepository,
        $metricConverter,
        $objectDetacher,
        $stepExecution,
        $jobRepository,
        ChannelInterface $channel,
        CategoryInterface $channelRoot,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        JobExecution $lastJobExecution,
        JobInstance $jobInstance
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('channel')->willReturn('mobile');
        $jobParameters->get('enabled')->willReturn('all');
        $jobParameters->get('updated')->willReturn('last_export');
        $jobParameters->get('families')->willReturn('');
        $jobParameters->get('completeness')->willReturn('at_least_one_complete');

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getCategory()->willReturn($channelRoot);
        $channelRoot->getId()->willReturn(42);
        $channel->getCode()->willReturn('mobile');

        $date = new \DateTime('2015-01-01 10:00:50');
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $lastJobExecution->getStartTime()->willReturn($date);
        $jobRepository->getLastJobExecution($jobInstance, BatchStatus::COMPLETED)->willReturn($lastJobExecution);

        $pqbFactory->create(['default_scope' => 'mobile'])
            ->shouldBeCalled()
            ->willReturn($pqb);
        $pqb->addFilter('enabled', Argument::cetera())->shouldNotBeCalled();
        $pqb->addFilter('completeness', '=', 100, [])->shouldBeCalled();
        $pqb->addFilter('categories.id', 'IN CHILDREN', [42], [])->shouldBeCalled();
        $pqb->addFilter('updated', '>', $date, [])->shouldBeCalled();
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

        $this->initialize();
        $this->read()->shouldReturn($product1);
        $this->read()->shouldReturn($product2);
        $this->read()->shouldReturn($product3);
        $this->read()->shouldReturn(null);
    }
}
