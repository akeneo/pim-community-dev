<?php

namespace spec\Pim\Bundle\EnrichBundle\Processor\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductTemplateUpdaterInterface;
use Pim\Bundle\EnrichBundle\Entity\MassEditJobConfiguration;
use Pim\Bundle\EnrichBundle\Entity\Repository\MassEditRepository;
use Pim\Bundle\EnrichBundle\Entity\Repository\MassEditRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ValidatorInterface;

class AddProductToVariantGroupProcessorSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $validator,
        MassEditRepositoryInterface $massEditRepository,
        GroupRepositoryInterface $groupRepository,
        ProductTemplateUpdaterInterface $templateUpdater
    ) {
        $this->beConstructedWith(
            $validator,
            $massEditRepository,
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
        MassEditRepository $massEditRepository,
        JobExecution $jobExecution,
        MassEditJobConfiguration $massEditJobConf,
        ProductTemplateInterface $productTemplate
    ) {
        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);

        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $massEditRepository->findOneBy(['jobExecution' => $jobExecution])->willReturn($massEditJobConf);
        $massEditJobConf->getConfiguration()->willReturn(
            json_encode(
                ['filters' => [], 'actions' => ['field' => 'variant_group', 'value' => 'variant_group_code',]]
            )
        );

        $groupRepository->findOneByIdentifier('variant_group_code')->willReturn($variantGroup);
        $product->getVariantGroup()->willReturn(null);
        $variantGroup->addProduct($product)->shouldBeCalled();
        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $templateUpdater->update($variantGroup->getProductTemplate(), [$product]);

        $stepExecution->incrementSummaryInfo('mass_edited')->shouldBeCalled($productTemplate);

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
        MassEditRepository $massEditRepository,
        JobExecution $jobExecution,
        MassEditJobConfiguration $massEditJobConf,
        ProductTemplateInterface $productTemplate
    ) {
        $violation = new ConstraintViolation('error2', 'spec', [], '', '', $product);
        $violations = new ConstraintViolationList([$violation, $violation]);
        $validator->validate($product)->willReturn($violations);

        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $massEditRepository->findOneBy(['jobExecution' => $jobExecution])->willReturn($massEditJobConf);
        $massEditJobConf->getConfiguration()->willReturn(
            json_encode(
                ['filters' => [], 'actions' => ['field' => 'variant_group', 'value' => 'variant_group_code',]]
            )
        );

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
