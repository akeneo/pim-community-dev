<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\MeasurementUnit;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\MeasurementUnitValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class MeasurementUnitValidatorSpec extends ObjectBehavior
{
    function let(
        PropertyAccessorInterface $propertyAccessor,
        MeasureManager $measureManager,
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $propertyAccessor->getValue(Argument::type('object'), Argument::type('string'))->will(
            function ($arguments) {
                $object = $arguments[0];
                $property = $arguments[1];

                return $object->$property;
            }
        );

        $this->beConstructedWith($propertyAccessor, $measureManager, $getAttributes);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(MeasurementUnitValidator::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['foo', new IsNull()]);
    }

    function it_does_nothing_if_the_unit_is_not_a_string(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            new TestValue('length', null),
            new MeasurementUnit(['attributeProperty' => 'attributeCode', 'unitProperty' => 'unit'])
        );
    }

    function it_does_nothing_if_the_attribute_code_is_not_a_string(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            new TestValue(['foo', 'bar'], 'CENTIMETER'),
            new MeasurementUnit(['attributeProperty' => 'attributeCode', 'unitProperty' => 'unit'])
        );
    }

    function it_does_nothing_if_the_attribute_does_not_exist(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode('foo')->shouldBeCalled()->willReturn(null);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            new TestValue('foo', 'CENTIMETER'),
            new MeasurementUnit(['attributeProperty' => 'attributeCode', 'unitProperty' => 'unit'])
        );
    }

    function it_does_not_add_a_violation_if_the_unit_matches_the_attribute(
        MeasureManager $measureManager,
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode('radius')->shouldBeCalled()->willReturn(
            new Attribute('radius', 'pim_catalog_metric', [], false, false, 'Length', null, null, 'metric', [])
        );
        $measureManager->unitCodeExistsInFamily('CENTIMETER', 'Length')->shouldBeCalled()->willReturn(true);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            new TestValue('radius', 'CENTIMETER'),
            new MeasurementUnit(['attributeProperty' => 'attributeCode', 'unitProperty' => 'unit'])
        );
    }

    function it_adds_a_violation_if_the_attribute_is_not_a_metric(
        MeasureManager $measureManager,
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(
            new Attribute('name', 'pim_catalog_text', [], false, false, null, null, null, 'string', [])
        );
        $measureManager->unitCodeExistsInFamily(
            Argument::type('string'),
            Argument::type('string')
        )->shouldNotBeCalled();

        $constraint = new MeasurementUnit(['attributeProperty' => 'attributeCode', 'unitProperty' => 'unit']);
        $context->buildViolation(
            $constraint->notMetricAttributeMessage,
            [
                '{{ attributeCode }}' => 'name',
                '{{ unitCode }}' => 'CENTIMETER',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('unit')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new TestValue('name', 'CENTIMETER'), $constraint);
    }

    function it_adds_a_violation_if_the_unit_does_not_match_the_measurement_family_of_the_attribute(
        MeasureManager $measureManager,
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('max_speed')->shouldBeCalled()->willReturn(
            new Attribute('max_speed', 'pim_catalog_metric', [], false, false, 'Speed', null, null, 'metric', [])
        );
        $measureManager->unitCodeExistsInFamily('CENTIMETER', 'Speed')->shouldBeCalled()->willReturn(false);

        $constraint = new MeasurementUnit(['attributeProperty' => 'attributeCode', 'unitProperty' => 'unit']);
        $context->buildViolation(
            $constraint->invalidUnitMessage,
            [
                '{{ attributeCode }}' => 'max_speed',
                '{{ unitCode }}' => 'CENTIMETER',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('unit')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new TestValue('max_speed', 'CENTIMETER'), $constraint);
    }
}

class TestValue
{
    public $attributeCode;
    public $unit;

    public function __construct($attributeCode, $unit)
    {
        $this->attributeCode = $attributeCode;
        $this->unit = $unit;
    }
}
