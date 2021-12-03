<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\DTO\SelectOptionDetails;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\AttributeColumnTypeShouldBeSelect;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\AttributeColumnTypeShouldBeSelectValidator;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AttributeColumnTypeShouldBeSelectValidatorSpec extends ObjectBehavior
{
    function let(
        GetAttributes $getAttributes,
        TableConfigurationRepository $tableConfigurationRepository,
        ExecutionContextInterface $executionContext
    ) {
        $this->beConstructedWith($getAttributes, $tableConfigurationRepository);
        $this->initialize($executionContext);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(AttributeColumnTypeShouldBeSelectValidator::class);
    }

    function it_throws_an_exception_when_validating_the_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [new SelectOptionDetails('nutrition', 'ingredient', 'salt', []), new NotBlank()]
        );
    }

    function it_throws_an_exception_when_validating_the_wrong_value()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [new \stdClass(), new AttributeColumnTypeShouldBeSelect()]
        );
    }

    function it_adds_a_violation_when_the_attribute_does_not_exist(
        GetAttributes $getAttributes,
        ExecutionContextInterface $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('unknown')->shouldBeCalled()->willReturn(null);
        $executionContext->buildViolation(Argument::type('string'), ['{{ attribute }}' => 'unknown'])
                         ->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('attribute')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new SelectOptionDetails('unknown', 'ingredient', 'salt', []),
            new AttributeColumnTypeShouldBeSelect()
        );
    }

    function it_adds_a_violation_when_the_attribute_is_not_a_table(
        GetAttributes $getAttributes,
        ExecutionContextInterface $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(
            new Attribute('name', 'pim_catalog_text', [], false, false, null, null, false, 'backend_type', [])
        );
        $executionContext->buildViolation(Argument::type('string'), ['{{ attribute }}' => 'name'])
                         ->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('attribute')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new SelectOptionDetails('name', 'ingredient', 'salt', []),
            new AttributeColumnTypeShouldBeSelect()
        );
    }

    function it_adds_a_violation_when_the_column_does_not_exist(
        GetAttributes $getAttributes,
        TableConfigurationRepository $tableConfigurationRepository,
        ExecutionContextInterface $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('nutrition')->shouldBeCalled()->willReturn(
            new Attribute('nutrition', 'pim_catalog_table', [], false, false, null, null, false, 'backend_type', [])
        );
        $tableConfigurationRepository->getByAttributeCode('nutrition')->shouldBeCalled()->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            ])
        );

        $executionContext->buildViolation(Argument::type('string'), [
            '{{ attribute }}' => 'nutrition',
            '{{ column }}' => 'unknown',
        ])->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('column')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new SelectOptionDetails('nutrition', 'unknown', 'salt', []),
            new AttributeColumnTypeShouldBeSelect()
        );
    }

    function it_adds_a_violation_if_the_column_is_not_a_select(
        GetAttributes $getAttributes,
        TableConfigurationRepository $tableConfigurationRepository,
        ExecutionContextInterface $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('nutrition')->shouldBeCalled()->willReturn(
            new Attribute('nutrition', 'pim_catalog_table', [], false, false, null, null, false, 'backend_type', [])
        );
        $tableConfigurationRepository->getByAttributeCode('nutrition')->shouldBeCalled()->willReturn(
            TableConfiguration::fromColumnDefinitions(
                [
                    SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
                    NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
                ]
            )
        );

        $executionContext->buildViolation(
            Argument::type('string'),
            [
                '{{ attribute }}' => 'nutrition',
                '{{ column }}' => 'quantity',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('column')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new SelectOptionDetails('nutrition', 'quantity', 'salt', []),
            new AttributeColumnTypeShouldBeSelect()
        );
    }

    function it_does_not_add_a_violation_for_a_valid_attribute_column_combination(
        GetAttributes $getAttributes,
        TableConfigurationRepository $tableConfigurationRepository,
        ExecutionContextInterface $executionContext
    ) {
        $getAttributes->forCode('nutrition')->shouldBeCalled()->willReturn(
            new Attribute('nutrition', 'pim_catalog_table', [], false, false, null, null, false, 'backend_type', [])
        );
        $tableConfigurationRepository->getByAttributeCode('nutrition')->shouldBeCalled()->willReturn(
            TableConfiguration::fromColumnDefinitions(
                [
                    SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
                    NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
                ]
            )
        );

        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            new SelectOptionDetails('nutrition', 'ingredient', 'salt', []),
            new AttributeColumnTypeShouldBeSelect()
        );
    }
}
