<?php

namespace spec\Pim\Component\Connector\Writer\Database;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\ProductInterface;

class ProductWriterSpec extends ObjectBehavior
{
    function let(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        EntityManagerClearerInterface $cacheClearer,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($versionManager, $productSaver, $cacheClearer);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Component\Connector\Writer\Database\ProductWriter');
    }

    function it_is_an_item_writer()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Item\ItemWriterInterface');
    }

    function it_is_step_execution_aware()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_saves_items(
        $productSaver,
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);

        $items = [$product1, $product2];

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);

        $productSaver->saveAll($items)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();
        $this->write($items);
    }

    function it_increments_summary_info(
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);

        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();

        $this->write([$product1, $product2]);
    }

    function it_clears_cache(
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);

        $items = [$product1, $product2];

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $this->write($items);
    }
}
