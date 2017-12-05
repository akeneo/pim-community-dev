<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Connector\Processor\Denormalization\Product\AddParent;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddToExistingProductModelProcessorSpec extends ObjectBehavior
{
    function let(
        AddParent $addParent,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $addParent,
            $validator
        );
    }

    function it_sets_parent_to_product(
        $addParent,
        $validator,
        ProductInterface $product,
        ProductInterface $updatedProduct,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ConstraintViolationListInterface $violations
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('actions')->willReturn([['value' => '42']]);
        $addParent->to($product, '42')->willReturn($updatedProduct);
        $validator->validate($updatedProduct)->willReturn($violations);
        $violations->count()->willReturn(0);
        $violations->rewind()->willReturn(null);
        $violations->valid()->willReturn(null);

        $this->process($product);
    }

    function it_adds_warning_on_exception(
        $addParent,
        ProductInterface $product,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        InvalidArgumentException $exception
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('actions')->willReturn([['value' => '42']]);
        $addParent->to($product, '42')->willThrow('Pim\Component\Catalog\Exception\InvalidArgumentException');
        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();

        $this->process($product);
    }

    function it_adds_warning_for_variant_product(
        VariantProductInterface $product,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('actions')->willReturn([['value' => '42']]);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();
        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();

        $this->process($product);
    }
}
