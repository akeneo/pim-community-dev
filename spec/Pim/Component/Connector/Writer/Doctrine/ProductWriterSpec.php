<?php

namespace spec\Pim\Component\Connector\Writer\Doctrine;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;

class ProductWriterSpec extends ObjectBehavior
{
    function let(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        BulkObjectDetacherInterface $detacher
    ) {
        $this->beConstructedWith($versionManager, $productSaver, $detacher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Component\Connector\Writer\Doctrine\ProductWriter');
    }

    function it_is_an_item_writer()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Item\ItemWriterInterface');
    }

    function it_is_step_execution_aware()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_is_a_configurable_step_element()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
    }

    function it_provides_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([
            'realTimeVersioning' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.import.realTimeVersioning.label',
                    'help'  => 'pim_connector.import.realTimeVersioning.help'
                ]
            ]
        ]);
    }

    function it_is_configurable()
    {
        $this->isRealTimeVersioning()->shouldReturn(true);

        $this->setRealTimeVersioning(false);

        $this->isRealTimeVersioning()->shouldReturn(false);
    }

    function it_saves_items(
        $productSaver,
        StepExecution $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $items = [$product1, $product2];

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);

        $this->setStepExecution($stepExecution);
        $productSaver->saveAll($items, ['recalculate' => false])->shouldBeCalled();
        $this->write($items);
    }

    function it_increments_summary_info(
        StepExecution $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);

        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->write([$product1, $product2]);
    }

    function it_clears_cache(
        StepExecution $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $items = [$product1, $product2];

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);

        $this->setStepExecution($stepExecution);
        $this->write($items);
    }
}
