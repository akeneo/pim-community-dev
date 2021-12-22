<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\RecordColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\ImmutableReferenceEntityIdentifier;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\ImmutableReferenceEntityIdentifierValidator;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ImmutableReferenceEntityIdentifierValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context, TableConfigurationRepository $tableConfigurationRepository): void
    {
        $tableConfigurationRepository->getByAttributeCode(Argument::any())->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(
                    [
                        'id' => ColumnIdGenerator::generateAsString('code'),
                        'code' => 'code',
                        'data_type' => 'select',
                        'is_required_for_completeness' => true,
                    ]
                ),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            ])
        );

        $this->beConstructedWith($tableConfigurationRepository);
        $this->initialize($context);
    }

    function it_is_initializable(): void
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ImmutableReferenceEntityIdentifierValidator::class);
    }

    function it_throws_an_exception_when_passed_the_wrong_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [[], new IsString()]);
    }

    function it_does_nothing_if_the_value_is_not_an_attribute(ExecutionContextInterface $context): void
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new ImmutableReferenceEntityIdentifier());
    }

    function it_does_nothing_if_the_value_is_not_a_table_attribute(
        ExecutionContextInterface $context,
        AttributeInterface $attribute
    ): void {
        $attribute->getType()->willReturn(AttributeTypes::DATE);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attribute, new ImmutableReferenceEntityIdentifier());
    }

    function it_does_nothing_if_the_attribute_code_is_null(
        ExecutionContextInterface $context,
        AttributeInterface $attribute
    ): void {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getCode()->willReturn(null);
        $attribute->getRawTableConfiguration()->willReturn([
            ['code' => 'code', 'data_type' => 'select'],
            ['code' => 'quantity', 'data_type' => 'number'],
            ['code' => 'brand', 'data_type' => RecordColumn::DATATYPE, 'reference_entity_identifier' => 'brands'],
        ]);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attribute, new ImmutableReferenceEntityIdentifier());
    }

    function it_does_nothing_if_the_raw_table_configuration_is_null(
        ExecutionContextInterface $context,
        AttributeInterface $attribute
    ): void {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getRawTableConfiguration()->willReturn(null);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attribute, new ImmutableReferenceEntityIdentifier());
    }

    function it_does_nothing_if_the_raw_table_configuration_is_not_valid(
        ExecutionContextInterface $context,
        AttributeInterface $attribute
    ): void {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getCode()->willReturn('test');
        $attribute->getRawTableConfiguration()->willReturn(['invalid']);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attribute, new ImmutableReferenceEntityIdentifier());
    }

    function it_does_nothing_if_the_attribute_is_new(
        ExecutionContextInterface $context,
        TableConfigurationRepository $tableConfigurationRepository,
        AttributeInterface $attribute
    ): void {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getCode()->willReturn('attribute');
        $attribute->getRawTableConfiguration()->willReturn([
            ['code' => 'code', 'data_type' => 'select'],
            ['code' => 'quantity', 'data_type' => 'number'],
            ['code' => 'brand', 'data_type' => RecordColumn::DATATYPE, 'reference_entity_identifier' => 'brands'],
        ]);
        $tableConfigurationRepository->getByAttributeCode('attribute')->shouldBeCalled()->willThrow(
            TableConfigurationNotFoundException::forAttributeCode('attribute')
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attribute, new ImmutableReferenceEntityIdentifier());
    }

    function it_does_not_add_any_violation_if_attribute_has_no_reference_entity_column(
        ExecutionContextInterface $context,
        AttributeInterface $attribute
    ): void {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getCode()->willReturn('test');
        $attribute->getRawTableConfiguration()->willReturn([
            ['code' => 'ingredients', 'data_type' => 'select'],
            ['code' => 'quantity', 'data_type' => 'number'],
        ]);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attribute, new ImmutableReferenceEntityIdentifier());
    }

    function it_does_not_add_any_violation_if_the_reference_entity_column_is_new(
        ExecutionContextInterface $context,
        TableConfigurationRepository $tableConfigurationRepository,
        AttributeInterface $attribute
    ): void {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getCode()->willReturn('attribute');
        $attribute->getRawTableConfiguration()->willReturn([
            ['code' => 'code', 'data_type' => 'select'],
            ['code' => 'quantity', 'data_type' => 'number'],
            ['code' => 'brand', 'data_type' => RecordColumn::DATATYPE, 'reference_entity_identifier' => 'brands'],
        ]);
        $tableConfigurationRepository->getByAttributeCode('attribute')->shouldBeCalled()->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(
                    [
                        'id' => ColumnIdGenerator::generateAsString('code'),
                        'code' => 'code',
                        'data_type' => 'select',
                        'is_required_for_completeness' => true,
                    ]
                ),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            ])
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attribute, new ImmutableReferenceEntityIdentifier());
    }

    function it_does_not_add_any_violation_if_the_reference_entity_identifier_was_not_updated(
        ExecutionContextInterface $context,
        TableConfigurationRepository $tableConfigurationRepository,
        AttributeInterface $attribute
    ): void {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getCode()->willReturn('attribute');
        $attribute->getRawTableConfiguration()->willReturn([
            ['code' => 'code', 'data_type' => 'select'],
            ['code' => 'quantity', 'data_type' => 'number'],
            ['code' => 'supplier', 'data_type' => RecordColumn::DATATYPE, 'reference_entity_identifier' => 'brands'],
        ]);
        $tableConfigurationRepository->getByAttributeCode('attribute')->shouldBeCalled()->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(
                    [
                        'id' => ColumnIdGenerator::generateAsString('code'),
                        'code' => 'code',
                        'data_type' => 'select',
                        'is_required_for_completeness' => true,
                    ]
                ),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
                RecordColumn::fromNormalized(
                    [
                        'id' => ColumnIdGenerator::supplier(),
                        'code' => 'supplier',
                        'reference_entity_identifier' => 'brands',
                    ]
                ),
            ])
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attribute, new ImmutableReferenceEntityIdentifier());
    }

    function it_does_not_add_any_violation_if_the_reference_entity_identifier_does_not_have_the_same_case(
        ExecutionContextInterface $context,
        TableConfigurationRepository $tableConfigurationRepository,
        AttributeInterface $attribute
    ): void {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getCode()->willReturn('attribute');
        $attribute->getRawTableConfiguration()->willReturn([
            ['code' => 'code', 'data_type' => 'select'],
            ['code' => 'quantity', 'data_type' => 'number'],
            ['code' => 'supplier', 'data_type' => RecordColumn::DATATYPE, 'reference_entity_identifier' => 'BRANDS'],
        ]);
        $tableConfigurationRepository->getByAttributeCode('attribute')->shouldBeCalled()->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(
                    [
                        'id' => ColumnIdGenerator::generateAsString('code'),
                        'code' => 'code',
                        'data_type' => 'select',
                        'is_required_for_completeness' => true,
                    ]
                ),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
                RecordColumn::fromNormalized(
                    [
                        'id' => ColumnIdGenerator::supplier(),
                        'code' => 'supplier',
                        'reference_entity_identifier' => 'brands',
                    ]
                ),
            ])
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attribute, new ImmutableReferenceEntityIdentifier());
    }

    function it_adds_a_violation_if_the_reference_entity_identifier_was_updated(
        ExecutionContextInterface $context,
        TableConfigurationRepository $tableConfigurationRepository,
        AttributeInterface $attribute,
        ConstraintViolationBuilderInterface $violationBuilder,
    ): void {
        $attribute->getType()->willReturn(AttributeTypes::TABLE);
        $attribute->getCode()->willReturn('attribute');
        $attribute->getRawTableConfiguration()->willReturn([
            ['code' => 'code', 'data_type' => 'select'],
            ['code' => 'quantity', 'data_type' => 'number'],
            ['code' => 'supplier', 'data_type' => RecordColumn::DATATYPE, 'reference_entity_identifier' => 'designers'],
        ]);
        $tableConfigurationRepository->getByAttributeCode('attribute')->shouldBeCalled()->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(
                    [
                        'id' => ColumnIdGenerator::generateAsString('code'),
                        'code' => 'code',
                        'data_type' => 'select',
                        'is_required_for_completeness' => true,
                    ]
                ),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
                RecordColumn::fromNormalized(
                    [
                        'id' => ColumnIdGenerator::supplier(),
                        'code' => 'supplier',
                        'reference_entity_identifier' => 'brands',
                    ]
                ),
            ])
        );

        $context->buildViolation(Argument::type('string'))->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder
            ->atPath('table_configuration[2].reference_entity_identifier')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, new ImmutableReferenceEntityIdentifier());
    }
}
