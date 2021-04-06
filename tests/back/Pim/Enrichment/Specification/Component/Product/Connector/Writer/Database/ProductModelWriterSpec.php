<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database;

use Akeneo\Pim\Enrichment\Component\ContextOrigin;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\ProductModelWriter;
use PhpSpec\ObjectBehavior;

class ProductModelWriterSpec extends ObjectBehavior
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
        $this->shouldHaveType(ProductModelWriter::class);
    }

    function it_is_an_item_writer()
    {
        $this->shouldHaveType(ItemWriterInterface::class);
    }

    function it_is_step_execution_aware()
    {
        $this->shouldHaveType(StepExecutionAwareInterface::class);
    }

    function it_saves_items(
        $productSaver,
        $stepExecution,
        ProductModelInterface $product1,
        ProductModelInterface $product2,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);
        $jobParameters->has('origin')->willReturn(true);
        $jobParameters->get('origin')->willReturn('MASS_EDIT');

        $items = [$product1, $product2];

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);

        $productSaver->saveAll($items, ['origin' => 'MASS_EDIT'])->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $this->write($items);
    }

    function it_saves_items_without_origin(
        $productSaver,
        $stepExecution,
        ProductModelInterface $product1,
        ProductModelInterface $product2,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);
        $jobParameters->has('origin')->willReturn(false);

        $items = [$product1, $product2];

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);

        $productSaver->saveAll($items, ['origin' => false])->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $this->write($items);
    }
}
