<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyRemoverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RemoveProductValueProcessorSpec extends ObjectBehavior
{
    function let(PropertyRemoverInterface $propertyRemover, ValidatorInterface $validator)
    {
        $this->beConstructedWith($propertyRemover, $validator);
    }

    function it_is_a_remover()
    {
        $this->shouldHaveType(AbstractProcessor::class);
    }

    function it_should_remove_value_from_product(
        $propertyRemover,
        $validator,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $configuration = [
            'filters' => [],
            'actions' => [['field' => 'categories', 'value' => ['office', 'bedroom'], 'options' => []]]
        ];
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);

        $propertyRemover->removeData($product, 'categories', ['office', 'bedroom'])->shouldBeCalled();

        $this->process($product);
    }

    function it_should_not_remove_invalid_value_from_product(
        $propertyRemover,
        $validator,
        ProductInterface $product,
        StepExecution $stepExecution,
        ConstraintViolationListInterface $violations,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $configuration = [
            'filters' => [],
            'actions' => [['field' => 'categories', 'value' => ['office', 'bedroom'], 'options' => []]]
        ];
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $validator->validate($product)->willReturn($violations);
        $violation = new ConstraintViolation('error2', 'spec', [], '', '', $product);
        $violations = new ConstraintViolationList([$violation, $violation]);
        $validator->validate($product)->willReturn($violations);

        $propertyRemover->removeData($product, 'categories', ['office', 'bedroom'])->shouldBeCalled();
        $this->setStepExecution($stepExecution);
        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();

        $this->process($product);
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }

}
