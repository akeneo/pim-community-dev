<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Writer\MassEdit;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Connector\Writer\MassEdit\ProductAndProductModelWriter;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModel;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Prophecy\Argument;

class ProductAndProductModelWriterSpec extends ObjectBehavior
{
    function let(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        EntityManagerClearerInterface $cacheClearer,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($versionManager, $productSaver, $productModelSaver, $cacheClearer);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAndProductModelWriter::class);
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
        $productModelSaver,
        $stepExecution,
        ProductInterface $product1,
        ProductModelInterface $productModel1,
        ProductInterface $product2,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);

        $items = [$product1, $productModel1, $product2];

        $products = $items;
        unset($products[1]);
        $productModels = $items;
        unset($productModels[0]);
        unset($productModels[2]);

        $productSaver->saveAll($products)->shouldBeCalled();
        $productModelSaver->saveAll($productModels)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();

        $this->write($items);
    }

    function it_increments_summary_info(
        $stepExecution,
        $productSaver,
        $productModelSaver,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductModelInterface $productModel1,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);

        $items = [$product1, $productModel1, $product2];

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);
        $productModel1->getId()->willReturn('89');

        $productSaver->saveAll(Argument::any())->shouldBeCalled();
        $productModelSaver->saveAll(Argument::any())->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalledTimes(2);

        $this->write($items);
    }

    function it_clears_cache(
        $cacheClearer,
        $stepExecution,
        ProductInterface $product,
        ProductModelInterface $productModel,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);

        $items = [$product, $productModel];

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();

        $this->write($items);
    }
}
