<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Writer\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateApplierInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\TransformBundle\Cache\CacheClearer;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Resource\Model\SaverInterface;
use Prophecy\Argument;

class VariantGroupValuesWriterSpec extends ObjectBehavior
{
    function let(
        SaverInterface $groupSaver,
        CacheClearer $cacheClearer,
        ProductTemplateApplierInterface $templateApplier,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($groupSaver, $cacheClearer, $templateApplier);
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
        $groupSaver,
        $cacheClearer,
        $stepExecution
    ) {
        $groupSaver->save($variantGroupOne)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('update')->shouldBeCalled();

        $groupSaver->save($variantGroupTwo)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('update')->shouldBeCalled();

        $cacheClearer->clear()->shouldBeCalled();

        $this->write([$variantGroupOne, $variantGroupTwo]);
    }

    function it_writes_a_variant_groups_and_copy_values_to_products(
        GroupInterface $variantGroup,
        $groupSaver,
        $cacheClearer,
        ProductTemplateInterface $productTemplate,
        Collection $productCollection,
        ProductInterface $productOne,
        ProductInterface $productTwo,
        $templateApplier,
        $stepExecution
    ) {
        $groupSaver->save($variantGroup)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('update')->shouldBeCalled();

        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $variantGroup->getProducts()->willReturn($productCollection);
        $productCollection->isEmpty()->willReturn(false);
        $productCollection->toArray()->willReturn([$productOne, $productTwo]);
        $productCollection->count()->willReturn(2);

        $templateApplier->apply($productTemplate, [$productOne, $productTwo])
            ->willReturn([])
            ->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('update_products', 2)->shouldBeCalled();

        $cacheClearer->clear()->shouldBeCalled();

        $this->write([$variantGroup]);
    }

    function it_writes_a_variant_groups_and_skip_copy_values_for_invalid_products(
        GroupInterface $variantGroup,
        $groupSaver,
        $cacheClearer,
        ProductTemplateInterface $productTemplate,
        Collection $productCollection,
        ProductInterface $validProduct,
        ProductInterface $invalidProduct,
        $templateApplier,
        $stepExecution
    ) {
        $groupSaver->save($variantGroup)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('update')->shouldBeCalled();

        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $variantGroup->getProducts()->willReturn($productCollection);
        $productCollection->isEmpty()->willReturn(false);
        $productCollection->toArray()->willReturn([$validProduct, $invalidProduct]);
        $productCollection->count()->willReturn(2);

        $templateApplier->apply($productTemplate, [$validProduct, $invalidProduct])
            ->willReturn(['sku-invalid' => ['message error one']])
            ->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('update_products', 1)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip_products', 1)->shouldBeCalled();
        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();

        $cacheClearer->clear()->shouldBeCalled();

        $this->write([$variantGroup]);
    }
}
