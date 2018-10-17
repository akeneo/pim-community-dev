<?php

namespace Specification\Akeneo\Channel\Component\Validator\Constraint;

use Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Validator\Constraint\ConversionUnits;
use Akeneo\Channel\Component\Validator\Constraint\ConversionUnitsValidator;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ConversionUnitsValidatorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        MeasureManager $measureManager,
        ExecutionContext $context
    ) {
        $this->beConstructedWith($attributeRepository, $measureManager);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConversionUnitsValidator::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(ConstraintValidator::class);
    }

    function it_does_not_add_violation_when_validating_null_value($context, ConversionUnits $constraint)
    {
        $context->buildViolation()->shouldNotBeCalled();
        $this->validate(null, $constraint);
    }

    function it_does_not_add_violation_when_validating_empty_array($context, ConversionUnits $constraint)
    {
        $context->buildViolation()->shouldNotBeCalled();
        $this->validate([], $constraint);
    }

    function it_does_not_add_violation_when_validating_string($context, ConversionUnits $constraint)
    {
        $context->buildViolation()->shouldNotBeCalled();
        $this->validate('attribute', $constraint);
    }

    function it_does_not_validate_a_conversion_unit_with_invalid_attribute(
        $attributeRepository,
        $context,
        ConversionUnits $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $conversionUnits = [
            'attributeCode' => 'conversionUnit',
        ];

        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn(null);
        $context->buildViolation(Argument::any(), Argument::any())
            ->willReturn($violation)
            ->shouldBeCalled();

        $violation->setParameter('%attributeCode%', Argument::any())
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->addViolation(Argument::any())->shouldBeCalled();

        $this->validate($conversionUnits, $constraint);
    }

    function it_does_not_validate_a_conversion_with_non_metric_attribute(
        $attributeRepository,
        $context,
        ConversionUnits $constraint,
        ConstraintViolationBuilderInterface $violation,
        AttributeInterface $attribute
    ) {
        $conversionUnits = [
            'attributeCode' => 'conversionUnit',
        ];

        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);

        $context->buildViolation(Argument::any(), Argument::any())
            ->willReturn($violation)
            ->shouldBeCalled();

        $violation->setParameter('%attributeCode%', Argument::any())
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->addViolation(Argument::any())->shouldBeCalled();

        $this->validate($conversionUnits, $constraint);
    }

    function it_does_not_validate_a_conversion_unit_with_unit_code(
        $attributeRepository,
        $measureManager,
        $context,
        ConversionUnits $constraint,
        ConstraintViolationBuilderInterface $violation,
        AttributeInterface $attribute
    ) {
        $conversionUnits = [
            'attributeCode' => 'conversionUnit',
        ];

        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::METRIC);
        $attribute->getMetricFamily()->willReturn(Argument::any());

        $measureManager->unitCodeExistsInFamily(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn(false);

        $context->buildViolation(Argument::any(), Argument::any())
            ->willReturn($violation)
            ->shouldBeCalled();

        $violation->setParameters([
            '%unitCode%'      => 'conversionUnit',
            '%attributeCode%' => 'attributeCode',
        ])
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->addViolation(Argument::any())->shouldBeCalled();

        $this->validate($conversionUnits, $constraint);
    }
}
