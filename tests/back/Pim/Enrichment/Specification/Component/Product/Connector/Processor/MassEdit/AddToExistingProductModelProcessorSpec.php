<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
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
        $product->isVariant()->willReturn(false);

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
        JobParameters $jobParameters
    ) {
        $product->isVariant()->willReturn(false);

        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('actions')->willReturn([['value' => '42']]);
        $addParent->to($product, '42')->willThrow(InvalidArgumentException::class);
        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();

        $this->process($product);
    }

    function it_adds_warning_for_variant_product(
        ProductInterface $product,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $product->isVariant()->willReturn(true);

        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('actions')->willReturn([['value' => '42']]);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();
        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();

        $this->process($product);
    }
}
