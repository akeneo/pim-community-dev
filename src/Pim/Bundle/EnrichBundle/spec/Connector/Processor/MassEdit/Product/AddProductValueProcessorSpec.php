<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Updater\PropertyAdderInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddProductValueProcessorSpec extends ObjectBehavior
{
    function let(
        PropertyAdderInterface $propertyAdder,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $propertyAdder,
            $validator
        );
    }

    function it_adds_values_to_product(
        $propertyAdder,
        $validator,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $configuration = ['filters' => [], 'actions' => [['field' => 'categories', 'value' => ['office', 'bedroom']]]];
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $propertyAdder->addData($product, 'categories', ['office', 'bedroom'])->shouldBeCalled();

        $this->setStepExecution($stepExecution);

        $this->process($product);
    }

    function it_adds_invalid_values_to_product(
        $propertyAdder,
        $validator,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $configuration = ['filters' => [], 'actions' => [['field' => 'categories', 'value' => ['office', 'bedroom']]]];
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $violation = new ConstraintViolation('error2', 'spec', [], '', '', $product);
        $violations = new ConstraintViolationList([$violation, $violation]);
        $validator->validate($product)->willReturn($violations);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $propertyAdder->addData($product, 'categories', ['office', 'bedroom'])->shouldBeCalled();
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
