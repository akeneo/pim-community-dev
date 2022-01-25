<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
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
    function let(ExecutionContextInterface $context, TableConfigurationRepository $tableConfigurationRepository, AttributeInterface $attribute): void
    {
        $attribute->getCode()->willReturn('nutrition');
        $context->getRoot()->willReturn($attribute);

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

    function it_does_nothing_if_the_value_is_not_an_array(ExecutionContextInterface $context): void
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new ImmutableReferenceEntityIdentifier());
    }

    function it_does_nothing_if_the_column_code_is_null(ExecutionContextInterface $context): void
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(['data_type' => ReferenceEntityColumn::DATATYPE, 'reference_entity_identifier' => 'brands'], new ImmutableReferenceEntityIdentifier());
    }

    function it_does_nothing_if_the_attribute_code_is_null(
        ExecutionContextInterface $context,
        AttributeInterface $attribute
    ): void {
        $attribute->getCode()->willReturn(null);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(['data_type' => ReferenceEntityColumn::DATATYPE, 'reference_entity_identifier' => 'brands'], new ImmutableReferenceEntityIdentifier());
    }

    function it_does_nothing_if_the_column_definition_is_not_an_array(ExecutionContextInterface $context): void
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('test', new ImmutableReferenceEntityIdentifier());
    }

    function it_does_nothing_if_the_column_is_not_a_reference_entity_column(
        ExecutionContextInterface $context,
        AttributeInterface $attribute
    ): void {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(['data_type' => SelectColumn::DATATYPE, 'code' => 'ingredient'], new ImmutableReferenceEntityIdentifier());
    }

    function it_does_nothing_if_the_column_does_not_have_a_datatype(
        ExecutionContextInterface $context,
        AttributeInterface $attribute
    ): void {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(['code' => 'brand', 'reference_entity_identifier' => 'brands'], new ImmutableReferenceEntityIdentifier());
    }

    function it_does_nothing_if_the_column_does_not_have_a_reference_entity_identifier(
        ExecutionContextInterface $context,
        AttributeInterface $attribute
    ): void {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(['data_type' => ReferenceEntityColumn::DATATYPE, 'code' => 'brand'], new ImmutableReferenceEntityIdentifier());
    }

    function it_does_nothing_if_the_reference_entity_identifier_is_not_a_string(
        ExecutionContextInterface $context,
        AttributeInterface $attribute
    ): void {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(['data_type' => ReferenceEntityColumn::DATATYPE, 'code' => 'brand', 'reference_entity_identifier' => 42], new ImmutableReferenceEntityIdentifier());
    }

    function it_does_nothing_if_the_attribute_is_new(
        ExecutionContextInterface $context,
        TableConfigurationRepository $tableConfigurationRepository,
        AttributeInterface $attribute
    ): void {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->shouldBeCalled()->willThrow(
            TableConfigurationNotFoundException::forAttributeCode('nutrition')
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(['data_type' => ReferenceEntityColumn::DATATYPE, 'code' => 'brand', 'reference_entity_identifier' => 'brands'], new ImmutableReferenceEntityIdentifier());
    }

    function it_does_not_add_any_violation_if_the_reference_entity_column_is_new(
        ExecutionContextInterface $context,
        TableConfigurationRepository $tableConfigurationRepository,
    ): void {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->shouldBeCalled()->willReturn(
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

        $this->validate(['data_type' => ReferenceEntityColumn::DATATYPE, 'code' => 'brand', 'reference_entity_identifier' => 'brands'], new ImmutableReferenceEntityIdentifier());
    }

    function it_does_not_add_any_violation_if_the_reference_entity_identifier_was_not_updated(
        ExecutionContextInterface $context,
        TableConfigurationRepository $tableConfigurationRepository,
    ): void {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->shouldBeCalled()->willReturn(
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
                ReferenceEntityColumn::fromNormalized(
                    [
                        'id' => ColumnIdGenerator::supplier(),
                        'code' => 'supplier',
                        'reference_entity_identifier' => 'brands',
                    ]
                ),
            ])
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(['data_type' => ReferenceEntityColumn::DATATYPE, 'code' => 'brand', 'reference_entity_identifier' => 'brands'], new ImmutableReferenceEntityIdentifier());
    }

    function it_does_not_add_any_violation_if_the_reference_entity_identifier_does_not_have_the_same_case(
        ExecutionContextInterface $context,
        TableConfigurationRepository $tableConfigurationRepository,
    ): void {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->shouldBeCalled()->willReturn(
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
                ReferenceEntityColumn::fromNormalized(
                    [
                        'id' => ColumnIdGenerator::supplier(),
                        'code' => 'supplier',
                        'reference_entity_identifier' => 'brands',
                    ]
                ),
            ])
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(['data_type' => ReferenceEntityColumn::DATATYPE, 'code' => 'brand', 'reference_entity_identifier' => 'BRANDS'], new ImmutableReferenceEntityIdentifier());
    }

    function it_adds_a_violation_if_the_reference_entity_identifier_was_updated(
        ExecutionContextInterface $context,
        TableConfigurationRepository $tableConfigurationRepository,
        ConstraintViolationBuilderInterface $violationBuilder,
    ): void {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->shouldBeCalled()->willReturn(
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
                ReferenceEntityColumn::fromNormalized(
                    [
                        'id' => ColumnIdGenerator::generateAsString('brand'),
                        'code' => 'brand',
                        'reference_entity_identifier' => 'brands',
                    ]
                ),
            ])
        );

        $context->buildViolation(Argument::type('string'))->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder
            ->atPath('reference_entity_identifier')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(['data_type' => ReferenceEntityColumn::DATATYPE, 'code' => 'brand', 'reference_entity_identifier' => 'designers'], new ImmutableReferenceEntityIdentifier());
    }
}
