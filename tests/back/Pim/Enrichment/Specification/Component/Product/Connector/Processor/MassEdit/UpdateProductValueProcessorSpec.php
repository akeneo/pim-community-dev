<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateProductValueProcessorSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $propertySetter,
            $validator
        );
    }

    function it_sets_values_to_product(
        $propertySetter,
        $validator,
        ProductInterface $product,
        StepExecution $stepExecution,
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

        $violations = new ConstraintViolationList([]);
        $validator->validate($product)->willReturn($violations);

        $propertySetter->setData($product, 'categories', ['office', 'bedroom'])->shouldBeCalled();

        $this->setStepExecution($stepExecution);

        $this->process($product);
    }

    function it_sets_invalid_values_to_product(
        $propertySetter,
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

        $propertySetter->setData($product, 'categories', ['office', 'bedroom'])->shouldBeCalled();
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
