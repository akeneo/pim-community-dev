<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints\DuplicateRecords;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class DuplicateRecordsValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context): void
    {
        $this->initialize($context);
    }

    function it_is_a_constraint_validator(): void
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_when_validating_anything_but_a_duplicate_options_constraint(): void
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [['wizard'], new IsNull()]);
        $this->shouldNotThrow(\Exception::class)->during('validate', [['wizard'], new DuplicateRecords(['attributeCode' => 'brands'])]);
    }

    function it_does_nothing_if_the_value_is_not_an_array(ExecutionContextinterface $context): void
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate('wizard', new DuplicateRecords(['attributeCode' => 'brands']));
    }

    function it_does_nothing_if_the_value_is_empty(ExecutionContextInterface $context): void
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate([], new DuplicateRecords(['attributeCode' => 'brands']));
    }

    function it_does_nothing_if_the_value_only_has_one_element(ExecutionContextInterface $context): void
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(['wizard'], new DuplicateRecords(['attributeCode' => 'brands']));
    }

    function it_does_not_build_a_violation_if_there_are_no_duplicates(ExecutionContextinterface $context): void
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(
            ['wizard', 'd&d'],
            new DuplicateRecords(['attributeCode' => 'brands'])
        );
    }

    function it_builds_a_violation_if_there_are_duplicate_options(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ): void {
        $value = ['wizard', 'd&d', 'wizard'];
        $constraint = new DuplicateRecords(['attributeCode' => 'brands']);

        $context->buildViolation(
            $constraint->message,
            [
                '{{ duplicate_codes }}' => 'wizard',
                '%count%' => 1,
                '%attribute_code%' => 'brands',
            ]
        )
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->setCode(DuplicateRecords::PROPERTY_CONSTRAINT)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    function it_builds_the_right_violation_if_an_option_appears_more_than_twice(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ): void {
        $value = ['wizard', 'd&d', 'warhammer', 'warhammer', 'd&d', 'hammer', 'war'];
        $constraint = new DuplicateRecords(['attributeCode' => 'brands']);

        $context->buildViolation(
            $constraint->message,
            [
                '{{ duplicate_codes }}' => 'warhammer, d&d',
                '%count%' => 2,
                '%attribute_code%' => 'brands',
            ]
        )
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->setCode(DuplicateRecords::PROPERTY_CONSTRAINT)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    function it_builds_an_exception_if_there_are_duplicate_options_with_a_different_case(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $value = ['wizard', 'd&d', 'WiZArd', 'D&d', 'war', 'hammer'];
        $constraint = new DuplicateRecords('brands');

        $context->buildViolation(
            $constraint->message,
            [
                '{{ duplicate_codes }}' => 'WiZArd, D&d',
                '%count%' => 2,
                '%attribute_code%' => 'brands',
            ]
        )
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->setCode(DuplicateRecords::PROPERTY_CONSTRAINT)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }
}
