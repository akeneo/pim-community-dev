<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValueCollection;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValueCollection\ProductShouldNotHaveTooManyCells;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValueCollection\ProductShouldNotHaveTooManyCellsValidator;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ProductShouldNotHaveTooManyCellsValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContext $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ProductShouldNotHaveTooManyCellsValidator::class);
    }

    function it_throws_an_exception_when_provided_with_the_wrong_constraint()
    {
        $valueCollection = new WriteValueCollection();
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [$valueCollection, new NotBlank()]
        );
    }

    function it_does_not_validate_when_no_value_collection_is_provided(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new ProductShouldNotHaveTooManyCells());
    }

    function it_does_not_add_violation_with_valid_value_collection(ExecutionContext $context)
    {
        $rows = [];
        for ($i = 0; $i < 8000; $i++) {
            $rows[] = ['ingredient' => sprintf('ingredient_%d', $i)];
        }
        $valueCollection = new WriteValueCollection([
            TableValue::value('nutrition', Table::fromNormalized($rows)),
            ScalarValue::value('autofocus', true),
        ]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($valueCollection, new ProductShouldNotHaveTooManyCells());
    }

    function it_adds_violation_with_invalid_value_collection_in_unique_table(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $rows = [];
        for ($i = 0; $i < 8001; $i++) {
            $rows[] = ['ingredient' => sprintf('ingredient_%d', $i)];
        }
        $nutritionValue = TableValue::value('nutrition', Table::fromNormalized($rows));
        $booleanValue = ScalarValue::value('autofocus', true);
        $valueCollection = new WriteValueCollection([$nutritionValue, $booleanValue]);

        $context->buildViolation(
            'pim_table_configuration.validation.product_value_collection.too_many_cells',
            ['{{ count }}' => 8001, '{{ limit }}' => 8000]
        )->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($valueCollection, new ProductShouldNotHaveTooManyCells());
    }

    function it_adds_violation_with_invalid_value_collection_in_several_tables(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $rows = [];
        for ($i = 0; $i < 2001; $i++) {
            $rows[] = ['ingredient' => sprintf('ingredient_%d', $i)];
        }
        $valueCollection = new WriteValueCollection([
            TableValue::localizableValue('nutrition', Table::fromNormalized($rows), 'fr_FR'),
            TableValue::localizableValue('nutrition', Table::fromNormalized($rows), 'en_US'),
            TableValue::localizableValue('nutrition', Table::fromNormalized($rows), 'de_DE'),
            TableValue::localizableValue('nutrition', Table::fromNormalized($rows), 'ch_CH'),
            ScalarValue::value('autofocus', true),
        ]);

        $context->buildViolation(
            'pim_table_configuration.validation.product_value_collection.too_many_cells',
            ['{{ count }}' => 8004, '{{ limit }}' => 8000]
        )->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($valueCollection, new ProductShouldNotHaveTooManyCells());
    }
}
