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
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\FirstColumnCodeCannotBeChanged;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\FirstColumnCodeCannotBeChangedValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

final class FirstColumnCodeCannotBeChangedValidatorSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository, ExecutionContext $context)
    {
        $this->beConstructedWith($attributeRepository);
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

    function it_throws_an_execption_when_value_is_not_an_attribute(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [
            [],
            new FirstColumnCodeCannotBeChanged(),
        ]);
    }

    function it_does_nothing_when_the_first_column_code_does_not_change(
        ExecutionContext $context,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attributeToValidate,
        AttributeInterface $formerAttribute
    ) {
        $attributeToValidate->getCode()->willReturn('table');
        $attributeRepository->findOneByIdentifier('table')->willReturn($formerAttribute);

        $formerAttribute->getRawTableConfiguration()->willReturn([
            ['code' => 'ingredients'],
            ['code' => 'quantity'],
        ]);
        $attributeToValidate->getRawTableConfiguration()->willReturn([
            ['code' => 'ingredients'],
            ['code' => 'new'],
        ]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attributeToValidate, new FirstColumnCodeCannotBeChanged());
    }

    function it_does_nothing_when_the_attribute_is_not_found(
        ExecutionContext $context,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attributeToValidate
    ) {
        $attributeToValidate->getCode()->willReturn('table');
        $attributeRepository->findOneByIdentifier('table')->willReturn(null);

        $attributeToValidate->getRawTableConfiguration()->willReturn([
            ['code' => 'ingredients'],
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

    function it_does_nothing_when_attribute_has_no_table_configuration(
        ExecutionContext $context,
        AttributeInterface $attributeToValidate
    ) {
        $attributeToValidate->getRawTableConfiguration()->willReturn(null);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attributeToValidate, new FirstColumnCodeCannotBeChanged());
    }

    function it_does_nothing_when_the_former_attribute_has_no_table_configuration(
        ExecutionContext $context,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attributeToValidate,
        AttributeInterface $formerAttribute
    ) {
        $attributeToValidate->getCode()->willReturn('table');
        $attributeRepository->findOneByIdentifier('table')->willReturn($formerAttribute);

        $formerAttribute->getRawTableConfiguration()->willReturn(null);
        $attributeToValidate->getRawTableConfiguration()->willReturn([
            ['code' => 'ingredients'],
            ['code' => 'new'],
        ]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attributeToValidate, new FirstColumnCodeCannotBeChanged());
    }

    function it_does_nothing_when_the_former_table_configuration_has_no_first_column(
        ExecutionContext $context,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attributeToValidate,
        AttributeInterface $formerAttribute
    ) {
        $attributeToValidate->getCode()->willReturn('table');
        $attributeRepository->findOneByIdentifier('table')->willReturn($formerAttribute);

        $formerAttribute->getRawTableConfiguration()->willReturn([]);
        $attributeToValidate->getRawTableConfiguration()->willReturn([
            ['code' => 'ingredients'],
            ['code' => 'new'],
        ]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attributeToValidate, new FirstColumnCodeCannotBeChanged());
    }

    function it_adds_a_violation_when_the_first_column_code_changes(
        ExecutionContext $context,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attributeToValidate,
        AttributeInterface $formerAttribute,
        ConstraintViolationBuilder $violationBuilder
    ) {
        $constraint = new FirstColumnCodeCannotBeChanged();
        $attributeToValidate->getCode()->willReturn('table');
        $attributeRepository->findOneByIdentifier('table')->willReturn($formerAttribute);

        $formerAttribute->getRawTableConfiguration()->willReturn([
            ['code' => 'new'],
            ['code' => 'quantity'],
        ]);
        $attributeToValidate->getRawTableConfiguration()->willReturn([
            ['code' => 'ingredients'],
            ['code' => 'quantity'],
        ]);

        $context->buildViolation($constraint->message)->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('[0].code')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($attributeToValidate, $constraint);
    }
}
