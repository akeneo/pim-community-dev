<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\ProductWriter;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

class ProductWriterSpec extends ObjectBehavior
{
    function let(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($versionManager, $productSaver);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductWriter::class);
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
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);
        $jobParameters->has('origin')->willReturn(true);
        $jobParameters->get('origin')->willReturn('IMPORT');

        $items = [$product1, $product2];

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);

        $productSaver->saveAll($items, ['origin' => 'IMPORT'])->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();
        $this->write($items);
    }

    function it_saves_items_without_origin(
        $productSaver,
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);
        $jobParameters->has('origin')->willReturn(false);

        $items = [$product1, $product2];

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);

        $productSaver->saveAll($items, ['origin' => null])->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();
        $this->write($items);
    }
}
