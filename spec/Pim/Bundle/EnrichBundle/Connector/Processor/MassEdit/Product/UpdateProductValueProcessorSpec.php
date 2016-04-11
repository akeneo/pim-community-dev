<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
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
        JobExecution $jobExecution
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $configuration = [
            'filters' => [],
            'actions' => [['field' => 'categories', 'value' => ['office', 'bedroom'], 'options' => []]]
        ];
        $this->setConfiguration($configuration);
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
        JobExecution $jobExecution
    ) {
        $configuration = [
            'filters' => [],
            'actions' => [['field' => 'categories', 'value' => ['office', 'bedroom'], 'options' => []]]
        ];
        $this->setConfiguration($configuration);
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

    function it_returns_the_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn(['actions' => []]);
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }
}
