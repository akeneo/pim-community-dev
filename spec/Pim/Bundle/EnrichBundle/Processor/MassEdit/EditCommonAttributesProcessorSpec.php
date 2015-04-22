<?php

namespace spec\Pim\Bundle\EnrichBundle\Processor\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\EnrichBundle\Entity\MassEditJobConfiguration;
use Pim\Bundle\EnrichBundle\Entity\Repository\MassEditRepository;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

class EditCommonAttributesProcessorSpec extends ObjectBehavior
{
    function let(
        ProductUpdaterInterface $productUpdater,
        ValidatorInterface $validator,
        ProductMassActionRepositoryInterface $massActionRepository,
        AttributeRepositoryInterface $attributeRepository,
        MassEditRepository $massEditRepository
    ) {
        $this->beConstructedWith(
            $productUpdater,
            $validator,
            $massActionRepository,
            $attributeRepository,
            $massEditRepository
        );
    }

    function it_returns_the_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }

    function it_sets_values_to_attributes(
        $validator,
        $productUpdater,
        FamilyInterface $family,
        AttributeInterface $attribute,
        AttributeRepository $attributeRepository,
        ProductInterface $product,
        StepExecution $stepExecution,
        MassEditRepository $massEditRepository,
        JobExecution $jobExecution,
        MassEditJobConfiguration $massEditJobConf
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $massEditRepository->findOneBy(['jobExecution' => $jobExecution])->willReturn($massEditJobConf);
        $massEditJobConf->getConfiguration()->willReturn(
            json_encode(
                [
                    'filters' => [],
                    'actions' => [['field' => 'categories', 'value' => ['office', 'bedroom'], 'options' => [],]]
                ]
            )
        );

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);

        $attributeRepository->findOneBy(['code' => 'categories'])->willReturn($attribute);
        $family->hasAttribute($attribute)->willReturn(true);
        $product->getFamily()->willReturn($family);
        $stepExecution->incrementSummaryInfo('mass_edited')->shouldBeCalled();

        $productUpdater->setData($product, 'categories', ['office', 'bedroom'], [])->shouldBeCalled();

        $this->process($product);
    }

    function it_sets_invalid_values_to_attributes(
        $validator,
        $productUpdater,
        FamilyInterface $family,
        AttributeInterface $attribute,
        AttributeRepository $attributeRepository,
        ProductInterface $product,
        ConstraintViolationListInterface $violations,
        StepExecution $stepExecution,
        MassEditRepository $massEditRepository,
        JobExecution $jobExecution,
        MassEditJobConfiguration $massEditJobConf
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $massEditRepository->findOneBy(['jobExecution' => $jobExecution])->willReturn($massEditJobConf);
        $massEditJobConf->getConfiguration()->willReturn(
            json_encode(
                [
                    'filters' => [],
                    'actions' => [['field' => 'categories', 'value' => ['office', 'bedroom'], 'options' => [],]]
                ]
            )
        );

        $validator->validate($product)->willReturn($violations);
        $violation = new ConstraintViolation('error2', 'spec', [], '', '', $product);
        $violations = new ConstraintViolationList([$violation, $violation]);
        $validator->validate($product)->willReturn($violations);

        $attributeRepository->findOneBy(['code' => 'categories'])->willReturn($attribute);
        $family->hasAttribute($attribute)->willReturn(true);
        $product->getFamily()->willReturn($family);

        $productUpdater->setData($product, 'categories', ['office', 'bedroom'], [])->shouldBeCalled();
        $this->setStepExecution($stepExecution);
        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();

        $this->process($product);
    }
}
