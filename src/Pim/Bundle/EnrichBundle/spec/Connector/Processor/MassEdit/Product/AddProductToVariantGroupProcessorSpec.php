<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddProductToVariantGroupProcessorSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $validator,
        GroupRepositoryInterface $groupRepository,
        ProductTemplateUpdaterInterface $templateUpdater
    ) {
        $this->beConstructedWith(
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
        ProductTemplateInterface $productTemplate,
        JobParameters $jobParameters
    ) {
        $configuration = ['filters' => [], 'actions' => ['field' => 'variant_group', 'value' => 'variant_group_code']];
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

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
        ProductTemplateInterface $productTemplate,
        JobParameters $jobParameters
    ) {
        $configuration = ['filters' => [], 'actions' => ['field' => 'variant_group', 'value' => 'variant_group_code']];
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

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

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }
}
