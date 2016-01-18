<?php

namespace spec\Pim\Component\Connector\Writer\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateApplierInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Prophecy\Argument;

class VariantGroupWriterSpec extends ObjectBehavior
{
    function let(
        BulkSaverInterface $groupSaver,
        BulkObjectDetacherInterface $detacher,
        ProductTemplateApplierInterface $productTplApplier,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($groupSaver, $detacher, $productTplApplier);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_writer()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_writes_some_variant_groups(
        GroupInterface $variantGroupOne,
        GroupInterface $variantGroupTwo,
        $stepExecution
    ) {
        $variantGroupOne->getId()->willReturn(null);
        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();

        $variantGroupTwo->getId()->willReturn(42);
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $variantGroupOne->getProductTemplate()->willReturn(null);
        $variantGroupOne->getProducts()->willReturn([]);
        $variantGroupTwo->getProductTemplate()->willReturn(null);
        $variantGroupTwo->getProducts()->willReturn([]);

        $this->write([$variantGroupOne, $variantGroupTwo]);
    }

    function it_writes_a_variant_groups_and_copy_values_to_products(
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        Collection $productCollection,
        ProductInterface $productOne,
        ProductInterface $productTwo,
        $productTplApplier,
        $stepExecution
    ) {
        $variantGroup->getId()->willReturn(42);
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $productTemplate->getValuesData()->willReturn(['something']);
        $variantGroup->getProducts()->willReturn($productCollection);
        $productCollection->isEmpty()->willReturn(false);
        $productCollection->toArray()->willReturn([$productOne, $productTwo]);
        $productCollection->count()->willReturn(2);

        $productTplApplier->apply($productTemplate, [$productOne, $productTwo])
            ->willReturn([])
            ->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('update_products', 2)->shouldBeCalled();

        $this->write([$variantGroup]);
    }

    function it_writes_a_variant_groups_and_skip_copy_values_for_invalid_products(
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        Collection $productCollection,
        ProductInterface $validProduct,
        ProductInterface $invalidProduct,
        $productTplApplier,
        $stepExecution
    ) {
        $variantGroup->getId()->willReturn(42);
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $productTemplate->getValuesData()->willReturn(['something']);
        $variantGroup->getProducts()->willReturn($productCollection);
        $productCollection->isEmpty()->willReturn(false);
        $productCollection->toArray()->willReturn([$validProduct, $invalidProduct]);
        $productCollection->count()->willReturn(2);

        $productTplApplier->apply($productTemplate, [$validProduct, $invalidProduct])
            ->willReturn(['sku-invalid' => ['message error one']])
            ->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('update_products', 1)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip_products', 1)->shouldBeCalled();
        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();

        $this->write([$variantGroup]);
    }

    function it_does_not_copy_values_to_products_when_template_is_empty(
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate,
        Collection $productCollection,
        ProductInterface $productOne,
        ProductInterface $productTwo,
        $productTplApplier,
        $stepExecution
    ) {
        $variantGroup->getId()->willReturn(42);
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $productTemplate->getValuesData()->willReturn([]);
        $variantGroup->getProducts()->willReturn($productCollection);
        $productCollection->isEmpty()->willReturn(false);
        $productCollection->toArray()->willReturn([$productOne, $productTwo]);
        $productCollection->count()->willReturn(2);

        $productTplApplier->apply($productTemplate, [$productOne, $productTwo])
            ->shouldNotBeCalled();

        $this->write([$variantGroup]);
    }
}
