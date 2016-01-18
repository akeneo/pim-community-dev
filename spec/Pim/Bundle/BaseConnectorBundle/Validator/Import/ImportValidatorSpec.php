<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Validator\Import;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Exception\DuplicateIdentifierException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfo;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportValidatorSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator)
    {
        $this->beConstructedWith($validator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Validator\Import\ImportValidator');
    }

    function it_is_an_import_validator()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Validator\Import\ImportValidatorInterface');
    }

    function it_throws_an_exception_if_there_is_a_duplicate_identifier(
        $validator,
        ProductInterface $product,
        ColumnInfo $columnInfo,
        ConstraintViolationList $violationList,
        \ArrayIterator $iterator
    ) {
        $product->getReference()->willReturn('sku-001');

        $columnInfo->getPropertyPath()->willReturn('unique_attribute');

        $validator->validate($product)->willReturn($violationList);

        $violationList->getIterator()->willReturn($iterator);

        $iterator->rewind()->willReturn(null);
        $iterator->valid()->willReturn(false);
        $iterator->next()->willReturn(null);

        $this->validate($product, [$columnInfo], [], []);
        $this
            ->shouldThrow(new DuplicateIdentifierException('sku-001', []))
            ->duringValidate($product, [$columnInfo], [], []);
    }

    function it_validates_a_property_from_an_entity(
        $validator,
        ProductInterface $product,
        ColumnInfo $columnInfo,
        ConstraintViolationListInterface $violationList
    ) {
        $product->getReference()->willReturn('sku-001');

        $columnInfo->getPropertyPath()->willReturn('name');

        $validator->validateProperty($product, 'name')->willReturn($violationList);

        $violationList->count()->willReturn(0);

        $this->validate($product, [$columnInfo], [], ['An error'])->shouldReturn(['An error']);
    }

    function it_validates_a_property_from_an_entity_which_has_error(
        $validator,
        ProductInterface $product,
        ColumnInfo $columnInfo,
        ConstraintViolationList $violationList,
        ConstraintViolationInterface $violation,
        \ArrayIterator $iterator
    ) {
        $product->getReference()->willReturn('sku-001');

        $columnInfo->getPropertyPath()->willReturn('name');
        $columnInfo->getLabel()->willReturn('Product name');

        $validator->validateProperty($product, 'name')->willReturn($violationList);

        $violationList->count()->willReturn(1);
        $violationList->getIterator()->willReturn($iterator);

        $iterator->rewind()->willReturn(null);
        $iterator->append($violation);
        $validReturns = [true, false];
        $iterator->valid()->will(function () use (&$validReturns) {
            $isValid = current($validReturns);
            next($validReturns);

            return $isValid;
        });
        $iterator->next()->willReturn(null);
        $iterator->current()->willReturn($violation);

        $violation->getMessageTemplate()->willReturn('An error occurs during validation');
        $violation->getMessageParameters()->willReturn([]);

        $this->validate($product, [$columnInfo], [], ['An error already here'])->shouldReturn([
            'Product name' => [
                [
                    'An error occurs during validation',
                    []
                ]
            ],
            0 => 'An error already here'
        ]);
    }

    function it_validates_an_entity_which_has_error(
        $validator,
        ProductInterface $product,
        ColumnInfo $columnInfo,
        ConstraintViolationList $violationList,
        ConstraintViolationInterface $violation1,
        ConstraintViolationInterface $violation2,
        \ArrayIterator $iterator
    ) {
        $product->getReference()->willReturn('sku-001');

        $columnInfo->getPropertyPath()->willReturn('unique_attribute');

        $validator->validate($product)->willReturn($violationList);

        $violationList->getIterator()->willReturn($iterator);

        $iterator->rewind()->willReturn(null);
        $iterator->append($violation1, $violation2);
        $validReturns = [true, true, false];
        $iterator->valid()->will(function () use (&$validReturns) {
            $isValid = current($validReturns);
            next($validReturns);

            return $isValid;
        });
        $iterator->next()->willReturn(null);
        $currentReturns = [$violation1, $violation2];
        $iterator->current()->will(function () use (&$currentReturns) {
            $current = current($currentReturns);
            next($currentReturns);

            return $current;
        });

        $violation1->getPropertyPath()->willReturn('unique_attribute');
        $violation1->getMessageTemplate()->willReturn('Attribute unique_attribute must be unique at column A12');
        $violation1->getMessageParameters()->willReturn([]);
        $violation2->getPropertyPath()->willReturn('unique_attribute');
        $violation2->getMessageTemplate()->willReturn('Attribute unique_attribute must be unique at column A15');
        $violation2->getMessageParameters()->willReturn([]);

        $this->validate($product, [$columnInfo], [], [])->shouldReturn([
            'unique_attribute' => [
                [
                    'Attribute unique_attribute must be unique at column A12',
                    []
                ],
                [
                    'Attribute unique_attribute must be unique at column A15',
                    []
                ]
            ]
        ]);
    }

    function it_validates_an_entity_which_has_no_error(
        $validator,
        ProductInterface $product,
        ColumnInfo $columnInfo,
        ConstraintViolationList $violationList,
        \ArrayIterator $iterator
    ) {
        $product->getReference()->willReturn('sku-001');

        $columnInfo->getPropertyPath()->willReturn('unique_attribute');

        $validator->validate($product)->willReturn($violationList);

        $violationList->getIterator()->willReturn($iterator);

        $iterator->rewind()->willReturn(null);
        $iterator->valid()->willReturn(false);
        $iterator->next()->willReturn(null);

        $this->validate($product, [$columnInfo], [], [])->shouldReturn([]);
    }

    function it_validates_an_entity_which_has_no_identifier(
        $validator,
        ProductInterface $product,
        ColumnInfo $columnInfo,
        ConstraintViolationList $violationList,
        \ArrayIterator $iterator
    ) {
        $product->getReference()->willReturn('sku-001');

        $columnInfo->getPropertyPath()->willReturn('unique_attribute');

        $validator->validate($product)->willReturn($violationList);

        $violationList->getIterator()->willReturn($iterator);

        $iterator->rewind()->willReturn(null);
        $iterator->valid()->willReturn(false);
        $iterator->next()->willReturn(null);

        $this->validate($product, [$columnInfo], [], [])->shouldReturn([]);
    }
}
