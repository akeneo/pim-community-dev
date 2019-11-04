<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\AttributeOptionsExist;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\AttributeOptionsExistValidator;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use Akeneo\Pim\Enrichment\Component\Product\Value\DateValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AttributeOptionsExistValidatorSpec extends ObjectBehavior
{
    function let(
        GetExistingAttributeOptionCodes $getExistingAttributeOptionCodes,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($getExistingAttributeOptionCodes);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_is_an_existing_attribute_options_validator()
    {
        $this->shouldBeAnInstanceOf(AttributeOptionsExistValidator::class);
    }

    function it_throws_an_exception_when_provided_with_an_invalid_constraint()
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', ['a_value', new IsString()]);
    }

    function it_only_validates_value_collections(
        GetExistingAttributeOptionCodes $getExistingAttributeOptionCodes,
        ExecutionContextInterface $context
    ) {
        $getExistingAttributeOptionCodes->fromOptionCodesByAttributeCode(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new AttributeOptionsExist());
    }

    function it_does_not_validate_the_values_if_there_are_no_simple_or_multi_select_values(
        GetExistingAttributeOptionCodes $getExistingAttributeOptionCodes,
        ExecutionContextInterface $context
    ) {
        $getExistingAttributeOptionCodes->fromOptionCodesByAttributeCode(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $values = new WriteValueCollection(
            [
                ScalarValue::value('sku', 'my_identifier'),
                ScalarValue::localizableValue('description', 'en_US', 'An awesome description'),
                DateValue::value('release_date', new \DateTime()),
            ]
        );

        $this->validate($values, new AttributeOptionsExist());
    }

    function it_does_not_build_any_violation_if_every_attribute_option_exists(
        GetExistingAttributeOptionCodes $getExistingAttributeOptionCodes,
        ExecutionContextInterface $context
    ) {
        $getExistingAttributeOptionCodes->fromOptionCodesByAttributeCode(
            [
                'color' => ['red', 'rouge'],
                'scopable_multi_select' => [
                    'valid_option',
                    'other_valid_option',
                    'other_valid_option',
                    'yet_another_valid_option',
                ],
            ]
        )->willReturn(
            [
                'color' => ['red', 'rouge'],
                'scopable_multi_select' => [
                    'valid_option',
                    'other_valid_option',
                    'yet_another_valid_option',
                ],
            ]
        );
        $context->buildViolation(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->validate(
            new WriteValueCollection(
                [
                    ScalarValue::value('name', 'My great product'),
                    OptionValue::localizableValue('color', 'red', 'en_US'),
                    OptionValue::localizableValue('color', 'rouge', 'fr_FR'),
                    OptionsValue::scopableValue('scopable_multi_select', ['valid_option', 'other_valid_option'], 'ecommerce'),
                    OptionsValue::scopableValue('scopable_multi_select', ['other_valid_option', 'yet_another_valid_option'], 'mobile'),
                ]
            ),
            new AttributeOptionsExist()
        );
    }

    function it_adds_violations_if_attribute_options_do_not_exist(
        GetExistingAttributeOptionCodes $getExistingAttributeOptionCodes,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        ConstraintViolationBuilderInterface $secondViolationBuilder
    ) {
        $constraint = new AttributeOptionsExist();
        $values = new WriteValueCollection(
            [
                ScalarValue::value('name', 'My great product'),
                OptionValue::localizableValue('color', 'red', 'en_US'),
                OptionValue::localizableValue('color', 'rouge', 'fr_FR'),
                OptionsValue::scopableValue(
                    'scopable_multi_select',
                    ['valid_option', 'invalid_option', 'other_invalid_option'],
                    'ecommerce'
                ),
            ]
        );

        $getExistingAttributeOptionCodes->fromOptionCodesByAttributeCode(
            [
                'color' => ['red', 'rouge'],
                'scopable_multi_select' => [
                    'valid_option',
                    'invalid_option',
                    'other_invalid_option',
                ],
            ]
        )->willReturn(
            [
                'color' => ['red'],
                'scopable_multi_select' => ['valid_option'],
            ]
        );

        $context->buildViolation($constraint->message, [
            '%attribute_code%' => 'color',
            '%invalid_option%' => 'rouge'
        ])->willReturn($violationBuilder);
        $violationBuilder->atPath('[color-<all_channels>-fr_FR]')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $context->buildViolation($constraint->messagePlural, [
            '%attribute_code%' => 'scopable_multi_select',
            '%invalid_options%' => 'invalid_option, other_invalid_option'
        ])->willReturn($secondViolationBuilder);
        $secondViolationBuilder->atPath('[scopable_multi_select-ecommerce-<all_locales>]')->willReturn($secondViolationBuilder);
        $secondViolationBuilder->addViolation()->shouldBecalled();

        $this->validate($values, $constraint);
    }

    function it_does_not_take_case_into_account_when_comparing_options(
        GetExistingAttributeOptionCodes $getExistingAttributeOptionCodes,
        ExecutionContextInterface $context
    ) {
        $getExistingAttributeOptionCodes->fromOptionCodesByAttributeCode(
            [
                'color' => ['RED', 'Green'],
                'fabrics' => ['cotton', 'WOOL', 'leATheR'],
            ]
        )->willReturn(
            [
                'color' => ['red', 'green'],
                'fabrics' => ['Cotton', 'Wool', 'Leather'],
            ]
        );
        $context->buildViolation(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->validate(
            new WriteValueCollection(
                [
                    OptionValue::localizableValue('color', 'RED', 'en_US'),
                    OptionValue::localizableValue('color', 'Green', 'fr_FR'),
                    OptionsValue::value('fabrics',['cotton', 'WOOL', 'leATheR']),
                ]
            ),
            new AttributeOptionsExist()
        );
    }
}
