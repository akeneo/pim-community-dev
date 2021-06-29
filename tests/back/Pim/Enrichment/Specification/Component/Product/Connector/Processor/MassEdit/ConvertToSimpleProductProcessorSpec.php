<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\ConvertToSimpleProductProcessor;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConvertToSimpleProductProcessorSpec extends ObjectBehavior
{
    function let(
        RemoveParentInterface $removeParent,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($removeParent, $validator);
        $this->setStepExecution($stepExecution);
    }

    function it_is_an_item_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_is_a_convert_to_simple_product_processor()
    {
        $this->shouldHaveType(ConvertToSimpleProductProcessor::class);
    }

    function it_skips_the_product_if_it_is_not_variant(StepExecution $stepExecution, ProductInterface $product)
    {
        $product->isVariant()->willReturn(false);
        $product->getIdentifier()->willReturn('foo');

        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();
        $stepExecution->addWarning(
            'pim_enrich.mass_edit_action.convert_to_simple_products.warning.non_variant_product',
            ['{{ identifier }}' => 'foo'],
            Argument::type(DataInvalidItem::class)
        )->shouldBeCalled();

        $this->process($product)->shouldReturn(null);
    }

    function it_skips_the_product_if_an_exception_is_raised_while_removing_the_parent(
        RemoveParentInterface $removeParent,
        StepExecution $stepExecution,
        ProductInterface $product
    ) {
        $product->isVariant()->willReturn(true);
        $removeParent->from($product)->shouldBeCalled()->willThrow(new InvalidArgumentException('error'));

        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();
        $stepExecution->addWarning('error', [], Argument::type(DataInvalidItem::class))->shouldBeCalled();

        $this->process($product)->shouldReturn(null);
    }

    function it_skips_the_product_if_it_is_not_valid_after_being_converted(
        RemoveParentInterface $removeParent,
        ValidatorInterface $validator,
        StepExecution $stepExecution,
        ProductInterface $product,
        ConstraintViolationInterface $violation
    ) {
        $product->isVariant()->willReturn(true);

        $violation->getInvalidValue()->willReturn(ScalarValue::value('sku', 'invalid'));
        $violation->getPropertyPath()->willReturn('values.sku');
        $violation->getMessage()->willReturn('Invalid SKU');
        $violations = new ConstraintViolationList([$violation->getWrappedObject()]);

        $removeParent->from($product)->shouldBeCalled();
        $validator->validate($product)->shouldBeCalled()->willReturn($violations);

        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalled();
        $stepExecution->addWarning(
            "values.sku: Invalid SKU: invalid\n",
            [],
            Argument::type(DataInvalidItem::class)
        )->shouldBeCalled();

        $this->process($product)->shouldReturn(null);
    }

    function it_converts_a_variant_product_to_a_simple_product(
        RemoveParentInterface $removeParent,
        ValidatorInterface $validator,
        ProductInterface $product
    ) {
        $product->isVariant()->willReturn(true);

        $removeParent->from($product)->shouldBeCalled();
        $validator->validate($product)->shouldBeCalled()->willReturn(new ConstraintViolationList([]));

        $this->process($product)->shouldReturn($product);
    }
}
