<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;
use Pim\Component\Connector\Model\JobConfigurationInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddProductToVariantGroupProcessorSpec extends ObjectBehavior
{
    function let(
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        ValidatorInterface $validator,
        GroupRepositoryInterface $groupRepository,
        ProductTemplateUpdaterInterface $templateUpdater
    ) {
        $this->beConstructedWith(
            $jobConfigurationRepo,
            $validator,
            $groupRepository,
            $templateUpdater
        );
    }

    function it_adds_values_to_product(
        $groupRepository,
        $validator,
        $templateUpdater,
        GroupInterface $variantGroup,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        ProductTemplateInterface $productTemplate
    ) {
        $configuration = ['filters' => [], 'actions' => ['field' => 'variant_group', 'value' => 'variant_group_code']];
        $this->setConfiguration($configuration);
        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $groupRepository->findOneByIdentifier('variant_group_code')->willReturn($variantGroup);
        $product->getVariantGroup()->willReturn(null);
        $variantGroup->addProduct($product)->shouldBeCalled();
        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $templateUpdater->update($variantGroup->getProductTemplate(), [$product]);

        $this->setStepExecution($stepExecution);

        $this->process($product);
    }

    function it_adds_invalid_values_to_product(
        $groupRepository,
        $validator,
        $templateUpdater,
        GroupInterface $variantGroup,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        ProductTemplateInterface $productTemplate
    ) {
        $configuration = ['filters' => [], 'actions' => ['field' => 'variant_group', 'value' => 'variant_group_code']];
        $this->setConfiguration($configuration);

        $violation = new ConstraintViolation('error2', 'spec', [], '', '', $product);
        $violations = new ConstraintViolationList([$violation, $violation]);
        $validator->validate($product)->willReturn($violations);

        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $groupRepository->findOneByIdentifier('variant_group_code')->willReturn($variantGroup);
        $product->getVariantGroup()->willReturn(null);
        $variantGroup->addProduct($product)->shouldBeCalled();
        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $templateUpdater->update($variantGroup->getProductTemplate(), [$product]);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();

        $this->setStepExecution($stepExecution);

        $this->process($product);
    }

    function it_returns_the_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }
}
