<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\ReferenceEntityIdentifierShouldOnlyBeSetOnRecordColumns;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\ReferenceEntityIdentifierShouldOnlyBeSetOnRecordColumnsValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ReferenceEntityIdentifierShouldOnlyBeSetOnRecordColumnsValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContext $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ReferenceEntityIdentifierShouldOnlyBeSetOnRecordColumnsValidator::class);
    }

    function it_throws_an_exception_with_the_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [['data_type' => 'record', 'code' => 'ingredient'], new NotBlank()]
        );
    }

    function it_adds_a_violation_if_reference_entity_identifier_is_set_on_a_non_record_column(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $context->buildViolation(Argument::type('string'), ['{{ data_type }}' => 'text'])->shouldBeCalled()->willReturn(
            $violationBuilder
        );
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            ['data_type' => 'text', 'code' => 'ingredient', 'reference_entity_identifier' => 'brands'],
            new ReferenceEntityIdentifierShouldOnlyBeSetOnRecordColumns(),
        );
    }

    function it_does_nothing_if_value_is_not_an_array(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate('toto', new ReferenceEntityIdentifierShouldOnlyBeSetOnRecordColumns());
    }

    function it_does_nothing_if_data_type_is_not_set(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(['code' => 'test', 'reference_entity_identifier' => 'brands'], new ReferenceEntityIdentifierShouldOnlyBeSetOnRecordColumns());
    }

    function it_does_nothing_if_data_type_is_not_a_string(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(
            ['data_type' => new NotBlank(), 'code' => 'test', 'reference_entity_identifier' => 'brands'],
            new ReferenceEntityIdentifierShouldOnlyBeSetOnRecordColumns()
        );
    }

    function it_does_not_add_a_violation_if_reference_entity_identifier_is_set_on_a_record_column(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(
            ['data_type' => 'record', 'code' => 'test', 'reference_entity_identifier' => 'brands'],
            new ReferenceEntityIdentifierShouldOnlyBeSetOnRecordColumns()
        );
    }

    function it_adds_a_violation_if_the_reference_entity_identifier_is_missing_on_a_record_column(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $context->buildViolation(Argument::type('string'))->shouldBeCalled()->willReturn(
            $violationBuilder
        );
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            ['data_type' => 'record', 'code' => 'brand'],
            new ReferenceEntityIdentifierShouldOnlyBeSetOnRecordColumns(),
        );
    }
}
