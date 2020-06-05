<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\ProductSource;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ProductSourceOptions;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ProductSourceOptionsValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ProductSourceOptionsValidatorSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($getAttributes);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ProductSourceOptionsValidator::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [new ProductSource([]), new IsNull()]);
    }

    function it_throws_an_exception_if_value_is_not_a_product_source()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['foo', new ProductSourceOptions()]);
    }

    function it_does_nothing_if_source_field_is_not_a_string(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new ProductSource(['field' => new \stdClass()]), new ProductSourceOptions());
    }

    function it_does_nothing_if_attribute_dopes_not_exist(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode('foo')->shouldBeCalled()->willReturn(null);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new ProductSource(['field' => 'foo']), new ProductSourceOptions());
    }

    function it_does_not_add_a_violation_if_format_is_set_with_a_date_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode('release_date')->shouldBeCalled()->willReturn(
            new Attribute('release_date', 'pim_catalog_date', [], false, false, null, null, null, 'date', [])
        );
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            new ProductSource(['field' => 'release_date', 'format' => 'Y-m-d H:i:s']),
            new ProductSourceOptions()
        );
    }

    function it_adds_a_violation_if_format_is_set_with_a_non_date_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(
            new Attribute('name', 'pim_catalog_text', [], false, false, null, null, null, 'string', [])
        );
        $constraint = new ProductSourceOptions();
        $context->buildViolation(
            $constraint->message,
            [
                '{{ key }}' => 'format',
                '{{ attribute }}' => 'name',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('format')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new ProductSource(['field' => 'name', 'format' => 'Y-m-d H:i:s']),
            $constraint
        );
    }

    function it_does_not_add_a_violation_if_label_locale_is_set_with_a_select_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode('designers')->shouldBeCalled()->willReturn(
            new Attribute(
                'designers',
                'akeneo_reference_entity_collection',
                ['reference_data_name' => 'designers'],
                false,
                false,
                null,
                null,
                null,
                'collections',
                []
            )
        );
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            new ProductSource(['field' => 'designers', 'label_locale' => 'en_US']),
            new ProductSourceOptions()
        );
    }

    function it_adds_a_violation_if_label_locale_is_set_with_a_non_select_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(
            new Attribute('name', 'pim_catalog_text', [], false, false, null, null, null, 'string', [])
        );
        $constraint = new ProductSourceOptions();
        $context->buildViolation(
            $constraint->message,
            [
                '{{ key }}' => 'label_locale',
                '{{ attribute }}' => 'name',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('labelLocale')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new ProductSource(['field' => 'name', 'label_locale' => 'en_US']),
            $constraint
        );
    }

    function it_does_not_add_a_violation_if_currency_is_set_with_a_price_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context
    ) {
        $getAttributes->forCode('price')->shouldBeCalled()->willReturn(
            new Attribute('price', 'pim_catalog_price_collection', [], false, false, null, null, null, 'price', [])
        );
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            new ProductSource(['field' => 'price', 'currency' => 'USD']),
            new ProductSourceOptions()
        );
    }

    function it_adds_a_violation_if_currency_is_set_with_a_non_price_attribute(
        GetAttributes $getAttributes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $getAttributes->forCode('name')->shouldBeCalled()->willReturn(
            new Attribute('name', 'pim_catalog_text', [], false, false, null, null, null, 'string', [])
        );
        $constraint = new ProductSourceOptions();
        $context->buildViolation(
            $constraint->message,
            [
                '{{ key }}' => 'currency',
                '{{ attribute }}' => 'name',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('currency')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new ProductSource(['field' => 'name', 'currency' => 'USD']),
            $constraint
        );
    }
}
