<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\IsValidAttribute;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\IsValidAttributeValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsValidAttributeValidatorSpec extends ObjectBehavior
{
    function let(
        GetAttributes $getAttributes,
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        PropertyAccessorInterface $propertyAccessor,
        ExecutionContextInterface $executionContext
    ) {
        $propertyAccessor->getValue(Argument::type('object'), Argument::type('string'))->will(
            function ($arguments) {
                $object = $arguments[0];
                $property = $arguments[1];

                return $object->$property;
            }
        );

        $this->beConstructedWith($getAttributes, $channelExistsWithLocale, $propertyAccessor);
        $this->initialize($executionContext);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(IsValidAttributeValidator::class);
    }

    function it_throws_an_exception_for_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [new \stdClass(), new IsNull()]);
    }

    function it_does_not_validate_non_object_values(
        GetAttributes $getAttributes,
        PropertyAccessorInterface $propertyAccessor,
        ExecutionContextInterface $executionContext
    ) {
        $propertyAccessor->getValue(Argument::any(), Argument::any())->shouldNotBeCalled();
        $getAttributes->forCode(Argument::any())->shouldNotBeCalled();
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('foo', $this->getConstraint());
    }

    function it_does_nothing_if_attribute_code_is_not_a_string(
        GetAttributes $getAttributes,
        ExecutionContextInterface $executionContext
    ) {
        $getAttributes->forCode(Argument::any())->shouldNotBeCalled();
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new TestObject(false, 'ecommerce', null), $this->getConstraint());
    }

    function it_does_nothing_if_attribute_does_not_exist(
        GetAttributes $getAttributes,
        ExecutionContextInterface $executionContext
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(null);
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new TestObject('name', null, null), $this->getConstraint());
    }

    function it_adds_a_violation_if_a_channel_is_set_for_a_non_scopable_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->buildAttribute('name', false, false));
        $executionContext->buildViolation(
            'pimee_catalog_rule.rule_definition.validation.attribute.unexpected_scope',
            [
                '{{ attributeCode }}' => 'name',
                '{{ channelCode }}' => 'ecommerce',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setInvalidValue('ecommerce')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new TestObject('name', 'ecommerce', null), $this->getConstraint());
    }

    function it_adds_a_violation_if_no_channel_is_set_for_a_scopable_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->buildAttribute('name', true, false));
        $executionContext->buildViolation(
            'pimee_catalog_rule.rule_definition.validation.attribute.missing_scope',
            [
                '{{ attributeCode }}' => 'name',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setInvalidValue(null)->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new TestObject('name', null, null), $this->getConstraint());
    }

    function it_adds_a_violation_if_the_channel_does_not_exist(
        GetAttributes $getAttributes,
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->buildAttribute('name', true, false));
        $channelExistsWithLocale->doesChannelExist('foo')->shouldBeCalled()->willReturn(false);
        $executionContext->buildViolation(
            'pimee_catalog_rule.rule_definition.validation.attribute.unknown_scope',
            [
                '{{ attributeCode }}' => 'name',
                '{{ channelCode }}' => 'foo',
            ],
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setInvalidValue('foo')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new TestObject('name', 'foo', null), $this->getConstraint());
    }

    function it_does_not_add_a_violation_if_scope_is_not_a_string(
        GetAttributes $getAttributes,
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $executionContext
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->buildAttribute('name', true, false));
        $channelExistsWithLocale->doesChannelExist(Argument::any())->shouldNotBeCalled();
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new TestObject('name', new \stdClass(), null), $this->getConstraint());
    }

    function it_adds_a_violation_if_a_locale_is_set_for_a_non_localizable_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->buildAttribute('name', false, false));
        $executionContext->buildViolation(
            'pimee_catalog_rule.rule_definition.validation.attribute.unexpected_locale',
            [
                '{{ attributeCode }}' => 'name',
                '{{ locale }}' => 'en_US',
            ],
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setInvalidValue('en_US')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new TestObject('name', null, 'en_US'), $this->getConstraint());
    }

    function it_adds_a_violation_if_no_locale_is_set_for_a_localizable_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->buildAttribute('name', false, true));
        $executionContext->buildViolation(
            'pimee_catalog_rule.rule_definition.validation.attribute.missing_locale',
            [
                '{{ attributeCode }}' => 'name',
            ],
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setInvalidValue(null)->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new TestObject('name', null, null), $this->getConstraint());
    }

    function it_does_not_add_a_violation_if_locale_is_not_a_string(
        GetAttributes $getAttributes,
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $executionContext
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->buildAttribute('name', false, true));
        $channelExistsWithLocale->isLocaleActive(Argument::any())->shouldNotBeCalled();
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new TestObject('name', null, ['123456']), $this->getConstraint());
    }

    function it_adds_a_violation_if_a_locale_is_not_active(
        GetAttributes $getAttributes,
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->buildAttribute('name', false, true));
        $channelExistsWithLocale->isLocaleActive('es_CA')->shouldBeCalled()->willReturn(false);
        $executionContext->buildViolation(
            'pimee_catalog_rule.rule_definition.validation.attribute.unknown_locale',
            [
                '{{ attributeCode }}' => 'name',
                '{{ locale }}' => 'es_CA',
            ],
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setInvalidValue('es_CA')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new TestObject('name', null, 'es_CA'), $this->getConstraint());
    }

    function it_adds_a_violation_if_a_locale_is_not_bound_to_the_channel_for_a_scopable_and_localizable_attribute(
        GetAttributes $getAttributes,
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn($this->buildAttribute('name', true, true));
        $channelExistsWithLocale->doesChannelExist('ecommerce')->shouldBeCalled()->willReturn(true);
        $channelExistsWithLocale->isLocaleActive('de_DE')->shouldBeCalled()->willReturn(true);
        $channelExistsWithLocale->isLocaleBoundToChannel('de_DE', 'ecommerce')->shouldBeCalled()->willReturn(false);
        $executionContext->buildViolation(
            'pimee_catalog_rule.rule_definition.validation.attribute.locale_not_bound_to_channel',
            [
                '{{ invalidLocale }}' => 'de_DE',
                '{{ channelCode }}' => 'ecommerce',
            ],
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setInvalidValue('de_DE')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new TestObject('name', 'ecommerce', 'de_DE'), $this->getConstraint());
    }

    function it_adds_a_violation_if_a_locale_is_not_part_of_the_available_locales_for_a_locale_specific_attribute(
        GetAttributes $getAttributes,
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        ExecutionContextInterface $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(
            $this->buildAttribute('name', false, true, ['en_US', 'fr_FR'])
        );
        $channelExistsWithLocale->isLocaleActive('de_DE')->shouldBeCalled()->willReturn(true);

        $executionContext->buildViolation(
            'pimee_catalog_rule.rule_definition.validation.attribute.invalid_specific_locale',
            [
                '{{ attributeCode }}' => 'name',
                '{{ expectedLocales }}' => 'en_US, fr_FR',
                '{{ invalidLocale }}' => 'de_DE',
            ],
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setInvalidValue('de_DE')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new TestObject('name', null, 'de_DE'), $this->getConstraint());
    }

    private function buildAttribute(string $code, bool $scopable, bool $localizable, array $availableLocales = [])
    {
        return new Attribute(
            $code,
            'pim_catalog_text',
            [],
            $localizable,
            $scopable,
            null,
            null,
            null,
            'string',
            $availableLocales
        );
    }

    private function getConstraint(): IsValidAttribute
    {
        return new IsValidAttribute(
            [
                'attributeProperty' => 'field',
                'channelProperty' => 'scope',
                'localeProperty' => 'locale',
            ]
        );
    }
}

class TestObject
{
    public $field;
    public $scope;
    public $locale;

    public function __construct($field, $scope, $locale)
    {
        $this->field = $field;
        $this->scope = $scope;
        $this->locale = $locale;
    }
}
