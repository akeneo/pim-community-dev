<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IdentifierAttributeCreationLimit;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IdentifierAttributeCreationLimitValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierAttributeCreationLimitValidatorSpec extends ObjectBehavior
{
    public function let(AttributeRepositoryInterface $repository, ExecutionContext $context): void
    {
        $this->beConstructedWith($repository, 5);
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(IdentifierAttributeCreationLimitValidator::class);
    }

    public function it_is_a_constraint_validator(): void
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['code', new NotBlank()]);
    }

    public function it_can_only_validate_an_attribute(AttributeRepositoryInterface $repository): void
    {
        $repository->getAttributeCodesByType(Argument::any())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new IdentifierAttributeCreationLimit());
    }

    public function it_can_only_validate_new_attribute_value(
        AttributeRepositoryInterface $repository,
        AttributeInterface $attribute
    ): void {
        $attribute->getId()->willReturn(1);

        $repository->getAttributeCodesByType(Argument::any())->shouldNotBeCalled();

        $this->validate($attribute, new IdentifierAttributeCreationLimit());
    }

    public function it_only_validates_identifier_attributes(
        AttributeRepositoryInterface $repository,
        AttributeInterface $attribute
    ): void {
        $attribute->getId()->willReturn(null);
        $attribute->getType()->willReturn(AttributeTypes::BOOLEAN);

        $repository->getAttributeCodesByType(Argument::any())->shouldNotBeCalled();
        $this->validate($attribute, new IdentifierAttributeCreationLimit());
    }

    public function it_should_build_a_violation_when_identifier_attribute_limit_is_reached(
        ExecutionContext $context,
        AttributeRepositoryInterface $repository,
        ConstraintViolationBuilderInterface $violationBuilder,
        AttributeInterface $attribute,
    ): void {
        $attribute->getId()->willReturn(null);
        $attribute->getType()->willReturn(AttributeTypes::IDENTIFIER);

        $repository->getAttributeCodesByType(AttributeTypes::IDENTIFIER)
            ->shouldBeCalledOnce()
            ->willReturn(['id_1', 'id_2', 'id_3', 'id_4', 'id_5']);

        $context->buildViolation('pim_catalog.constraint.identifier_attribute_limit_reached', ['{{limit}}' => 5])
            ->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, new IdentifierAttributeCreationLimit());
    }

    public function it_should_be_valid_when_identifier_attribute_is_under_limit(
        ExecutionContext $context,
        AttributeRepositoryInterface $repository,
        AttributeInterface $attribute
    ): void {
        $attribute->getId()->willReturn(null);
        $attribute->getType()->willReturn(AttributeTypes::IDENTIFIER);

        $repository->getAttributeCodesByType(AttributeTypes::IDENTIFIER)
            ->shouldBeCalledOnce()
            ->willReturn(['id_1', 'id_2']);

        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate($attribute, new IdentifierAttributeCreationLimit());
    }
}
