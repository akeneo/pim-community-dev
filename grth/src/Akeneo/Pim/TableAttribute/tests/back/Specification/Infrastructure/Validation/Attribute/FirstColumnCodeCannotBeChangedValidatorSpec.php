<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\FirstColumnCodeCannotBeChanged;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\FirstColumnCodeCannotBeChangedValidator;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

final class FirstColumnCodeCannotBeChangedValidatorSpec extends ObjectBehavior
{
    function let(TableConfigurationRepository $tableConfigurationRepository, ExecutionContext $context)
    {
        $this->beConstructedWith($tableConfigurationRepository);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(FirstColumnCodeCannotBeChangedValidator::class);
    }

    function it_throws_an_exception_when_provided_with_an_invalid_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [[], new NotBlank()]);
    }

    function it_throws_an_exception_when_value_is_not_an_attribute(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [
            [],
            new FirstColumnCodeCannotBeChanged(),
        ]);
    }

    function it_does_nothing_when_the_first_column_code_does_not_change(
        ExecutionContext $context,
        TableConfigurationRepository $tableConfigurationRepository,
        AttributeInterface $attributeToValidate
    ) {
        $attributeToValidate->getCode()->willReturn('table');
        $tableConfigurationRepository->getByAttributeCode('table')->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            ])
        );

        $attributeToValidate->getRawTableConfiguration()->willReturn([
            ['code' => 'ingredient'],
            ['code' => 'new'],
        ]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attributeToValidate, new FirstColumnCodeCannotBeChanged());
    }

    function it_does_nothing_when_the_first_column_code_does_not_change_with_case_insensitive(
        ExecutionContext $context,
        TableConfigurationRepository $tableConfigurationRepository,
        AttributeInterface $attributeToValidate,
        AttributeInterface $formerAttribute
    ) {
        $attributeToValidate->getCode()->willReturn('table');

        $tableConfigurationRepository->getByAttributeCode('table')->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            ])
        );
        $attributeToValidate->getRawTableConfiguration()->willReturn([
            ['code' => 'INGredient'],
            ['code' => 'new'],
        ]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attributeToValidate, new FirstColumnCodeCannotBeChanged());
    }

    function it_does_nothing_when_the_former_table_configuration_is_not_found(
        ExecutionContext $context,
        TableConfigurationRepository $tableConfigurationRepository,
        AttributeInterface $attributeToValidate
    ) {
        $attributeToValidate->getCode()->willReturn('table');
        $tableConfigurationRepository->getByAttributeCode('table')->willThrow(
            TableConfigurationNotFoundException::class
        );

        $attributeToValidate->getRawTableConfiguration()->willReturn([
            ['code' => 'ingredient'],
            ['code' => 'new'],
        ]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attributeToValidate, new FirstColumnCodeCannotBeChanged());
    }

    function it_does_nothing_when_new_table_configuration_has_not_first_column(
        ExecutionContext $context,
        AttributeInterface $attributeToValidate
    ) {
        $attributeToValidate->getRawTableConfiguration()->willReturn([]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attributeToValidate, new FirstColumnCodeCannotBeChanged());
    }

    function it_does_nothing_when_new_table_configuration_has_not_a_valid_first_column(
        ExecutionContext $context,
        AttributeInterface $attributeToValidate
    ) {
        $attributeToValidate->getRawTableConfiguration()->willReturn([
            ['code' => new \stdClass()],
            ['code' => 'quantity'],
        ]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attributeToValidate, new FirstColumnCodeCannotBeChanged());
    }

    function it_does_nothing_when_attribute_has_no_table_configuration(
        ExecutionContext $context,
        AttributeInterface $attributeToValidate
    ) {
        $attributeToValidate->getRawTableConfiguration()->willReturn(null);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attributeToValidate, new FirstColumnCodeCannotBeChanged());
    }

    function it_adds_a_violation_when_the_first_column_code_changes(
        ExecutionContext $context,
        TableConfigurationRepository $tableConfigurationRepository,
        AttributeInterface $attributeToValidate,
        AttributeInterface $formerAttribute,
        ConstraintViolationBuilder $violationBuilder
    ) {
        $constraint = new FirstColumnCodeCannotBeChanged();
        $attributeToValidate->getCode()->willReturn('table');
        $tableConfigurationRepository->getByAttributeCode('table')->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            ])
        );

        $attributeToValidate->getRawTableConfiguration()->willReturn([
            ['code' => 'new'],
            ['code' => 'quantity'],
        ]);

        $context->buildViolation($constraint->message)->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('[0].code')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($attributeToValidate, $constraint);
    }
}
