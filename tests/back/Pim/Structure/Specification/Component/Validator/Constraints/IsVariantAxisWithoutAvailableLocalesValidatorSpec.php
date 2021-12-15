<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql\AttributeIsAFamilyVariantAxis;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IsVariantAxisWithoutAvailableLocales;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IsVariantAxisWithoutAvailableLocalesValidator;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsVariantAxisWithoutAvailableLocalesValidatorSpec extends ObjectBehavior
{
    function let(AttributeIsAFamilyVariantAxis $attributeIsAFamilyVariantAxis, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($attributeIsAFamilyVariantAxis);
        $this->initialize($context);
    }

    function it_is_initialiazable()
    {
        $this->shouldBeAnInstanceOf(IsVariantAxisWithoutAvailableLocalesValidator::class);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldBeAnInstanceOf(ConstraintValidator::class);
    }

    function it_throws_an_exception_on_non_supported_constraint(Constraint $constraint)
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [
            'testValue',
            $constraint
        ]);
    }

    function it_does_nothing_on_unsupported_value(
        AttributeIsAFamilyVariantAxis $attributeIsAFamilyVariantAxis,
        IsVariantAxisWithoutAvailableLocales $constraint,
        ExecutionContextInterface $context,
    ) {
        $attributeIsAFamilyVariantAxis->execute(Argument::any())->shouldNotBeCalled();
        $context->buildViolation($constraint->message)->shouldNotBeCalled();

        $this->validate('non attribute', $constraint);
    }

    /**
     * Integration test to validate an attribute with invalid code (integer or null) will fail without a check on code type
     * @see GlobalConstraintsIntegration testCodeIsNotBlank
     * @see DataTypesIntegration testCodeIsString
     */
    function it_does_nothing_on_attribute_without_code(
        AttributeIsAFamilyVariantAxis $attributeIsAFamilyVariantAxis,
        IsVariantAxisWithoutAvailableLocales $constraint,
        AttributeInterface $attribute,
        ExecutionContextInterface $context,
    ) {
        $attribute->getCode()->willReturn(null);

        $attributeIsAFamilyVariantAxis->execute(Argument::any())->shouldNotBeCalled();
        $context->buildViolation($constraint->message)->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_does_not_create_any_violations_for_a_locale_specific_attribute_only(
        AttributeIsAFamilyVariantAxis $attributeIsAFamilyVariantAxis,
        IsVariantAxisWithoutAvailableLocales $constraint,
        ExecutionContextInterface $context,
        AttributeInterface $attribute,
    ) {
        $attribute->getCode()->willReturn('code');
        $attribute->isLocaleSpecific()->willReturn(true);

        $attributeIsAFamilyVariantAxis->execute('code')->willReturn(false);

        $context->buildViolation($constraint->message)->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_does_not_create_any_violations_for_a_family_variant_axis_attribute_only(
        AttributeIsAFamilyVariantAxis $attributeIsAFamilyVariantAxis,
        IsVariantAxisWithoutAvailableLocales $constraint,
        ExecutionContextInterface $context,
        AttributeInterface $attribute,
    ) {
        $attribute->getCode()->willReturn('code');
        $attribute->isLocaleSpecific()->willReturn(false);

        $attributeIsAFamilyVariantAxis->execute('code')->willReturn(true);

        $context->buildViolation($constraint->message)->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_does_not_create_any_violations_for_a_non_locale_specific_nor_variant_axis_attribute(
        AttributeIsAFamilyVariantAxis $attributeIsAFamilyVariantAxis,
        IsVariantAxisWithoutAvailableLocales $constraint,
        ExecutionContextInterface $context,
        AttributeInterface $attribute,
    ) {
        $attribute->getCode()->willReturn('code');
        $attribute->isLocaleSpecific()->willReturn(false);

        $attributeIsAFamilyVariantAxis->execute('code')->willReturn(false);

        $context->buildViolation($constraint->message)->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_builds_a_violation_for_a_locale_specific_and_variant_axis_attribute(
        AttributeIsAFamilyVariantAxis $attributeIsAFamilyVariantAxis,
        IsVariantAxisWithoutAvailableLocales $constraint,
        ExecutionContextInterface $context,
        AttributeInterface $attribute,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $attribute->getCode()->willReturn('code');
        $attribute->isLocaleSpecific()->willReturn(true);

        $attributeIsAFamilyVariantAxis->execute('code')->willReturn(true);

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->atPath($constraint->propertyPath)->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }
}
