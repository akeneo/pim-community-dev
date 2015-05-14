<?php

namespace spec\Pim\Bundle\EnrichBundle\Processor\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Model\JobConfigurationInterface;
use Pim\Bundle\BaseConnectorBundle\Model\Repository\JobConfigurationRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductFieldUpdaterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ValidatorInterface;

class AddProductValueProcessorSpec extends ObjectBehavior
{
    function let(
        ProductFieldUpdaterInterface $productFieldUpdater,
        ValidatorInterface $validator,
        JobConfigurationRepositoryInterface $jobConfigurationRepo
    ) {
        $this->beConstructedWith(
            $productFieldUpdater,
            $validator,
            $jobConfigurationRepo
        );
    }

    function it_adds_values_to_product(
        $productFieldUpdater,
        $validator,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration
    ) {
        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);

        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);
        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(['filters' => [], 'actions' => [['field' => 'categories', 'value' => ['office', 'bedroom']]]])
        );

        $productFieldUpdater->addData($product, 'categories', ['office', 'bedroom'])->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('mass_edited')->shouldBeCalled();

        $this->setStepExecution($stepExecution);

        $this->process($product);
    }

    function it_adds_invalid_values_to_product(
        $productFieldUpdater,
        $validator,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration
    ) {
        $violation = new ConstraintViolation('error2', 'spec', [], '', '', $product);
        $violations = new ConstraintViolationList([$violation, $violation]);
        $validator->validate($product)->willReturn($violations);

        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);
        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(['filters' => [], 'actions' => [['field' => 'categories', 'value' => ['office', 'bedroom']]]])
        );

        $productFieldUpdater->addData($product, 'categories', ['office', 'bedroom'])->shouldBeCalled();
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
