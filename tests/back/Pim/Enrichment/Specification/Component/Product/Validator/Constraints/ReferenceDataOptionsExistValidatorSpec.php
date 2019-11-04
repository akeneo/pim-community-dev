<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetExistingReferenceDataCodes;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ReferenceDataOptionsExist;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ReferenceDataOptionsExistValidator;
use Akeneo\Pim\Enrichment\Component\Product\Value\DateValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ReferenceDataOptionsExistValidatorSpec extends ObjectBehavior
{
    function let(
        GetExistingReferenceDataCodes $getExistingReferenceDataCodes,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($getExistingReferenceDataCodes, $attributeRepository);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataOptionsExistValidator::class);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_is_an_existing_attribute_options_validator()
    {
        $this->shouldBeAnInstanceOf(ReferenceDataOptionsExistValidator::class);
    }

    function it_throws_an_exception_when_provided_with_an_invalid_constraint()
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', ['a_value', new IsString()]);
    }

    function it_only_validates_value_collections(
        GetExistingReferenceDataCodes $getExistingReferenceDataCodes,
        ExecutionContextInterface $context
    ) {
        $getExistingReferenceDataCodes->fromReferenceDataNameAndCodes(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new ReferenceDataOptionsExist());
    }

    function it_does_not_validate_the_values_if_there_are_no_simple_or_multi_reference_data_values(
        GetExistingReferenceDataCodes $getExistingReferenceDataCodes,
        ExecutionContextInterface $context
    ) {
        $getExistingReferenceDataCodes->fromReferenceDataNameAndCodes(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $values = new WriteValueCollection(
            [
                ScalarValue::value('sku', 'my_identifier'),
                ScalarValue::localizableValue('description', 'en_US', 'An awesome description'),
                DateValue::value('release_date', new \DateTime()),
            ]
        );

        $this->validate($values, new ReferenceDataOptionsExist());
    }

    function it_does_not_build_any_violation_if_every_reference_data_option_exists(
        GetExistingReferenceDataCodes $getExistingReferenceDataCodes,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ExecutionContextInterface $context,
        AttributeInterface $laceColor,
        AttributeInterface $soleColor,
        AttributeInterface $fabrics
    ) {
        $laceColor->getReferenceDataName()->willReturn('color');
        $attributeRepository->findOneByIdentifier('lace_color')->willReturn($laceColor);
        $soleColor->getReferenceDataName()->willReturn('color');
        $attributeRepository->findOneByIdentifier('sole_color')->willReturn($soleColor);
        $fabrics->getReferenceDataName()->willReturn('fabrics');
        $attributeRepository->findOneByIdentifier('fabrics')->willReturn($fabrics);

        $getExistingReferenceDataCodes->fromReferenceDataNameAndCodes('color', ['red', 'rouge'])
                                      ->willReturn(['red', 'rouge']);
        $getExistingReferenceDataCodes->fromReferenceDataNameAndCodes('fabrics', ['cotton', 'leather', 'wool'])
                                      ->willReturn(['cotton', 'leather', 'wool']);

        $context->buildViolation(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->validate(
            new WriteValueCollection(
                [
                    ScalarValue::value('name', 'My great product'),
                    ReferenceDataValue::localizableValue('sole_color', 'red', 'en_US'),
                    ReferenceDataValue::localizableValue('lace_color', 'rouge', 'fr_FR'),
                    ReferenceDataCollectionValue::scopableValue(
                        'fabrics',
                        ['cotton', 'leather'],
                        'ecommerce'
                    ),
                    ReferenceDataCollectionValue::scopableValue(
                        'fabrics',
                        ['leather', 'wool'],
                        'mobile'
                    ),
                ]
            ),
            new ReferenceDataOptionsExist()
        );
    }

    function it_adds_violations_if_reference_data_options_do_not_exist(
        GetExistingReferenceDataCodes $getExistingReferenceDataCodes,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ExecutionContextInterface $context,
        AttributeInterface $laceColor,
        AttributeInterface $fabrics,
        ConstraintViolationBuilderInterface $violationBuilder,
        ConstraintViolationBuilderInterface $secondViolationBuilder
    ) {
        $laceColor->getReferenceDataName()->willReturn('color');
        $attributeRepository->findOneByIdentifier('lace_color')->willReturn($laceColor);
        $fabrics->getReferenceDataName()->willReturn('fabrics');
        $attributeRepository->findOneByIdentifier('fabrics')->willReturn($fabrics);

        $getExistingReferenceDataCodes->fromReferenceDataNameAndCodes('color', ['red', 'rouge'])
                                      ->willReturn(['red']);
        $getExistingReferenceDataCodes->fromReferenceDataNameAndCodes('fabrics', ['leather',  'cotton', 'wool'])
                                      ->willReturn(['cotton']);

        $constraint = new ReferenceDataOptionsExist();
        $values = new WriteValueCollection(
            [
                ScalarValue::value('name', 'My great product'),
                ReferenceDataValue::localizableValue('lace_color', 'red', 'en_US'),
                ReferenceDataValue::localizableValue('lace_color', 'rouge', 'fr_FR'),
                ReferenceDataCollectionValue::scopableValue(
                    'fabrics',
                    ['leather', 'cotton', 'wool'],
                    'ecommerce'
                ),
            ]
        );

        $context->buildViolation(
            $constraint->message,
            [
                '%attribute_code%' => 'lace_color',
                '%reference_data_name%' => 'color',
                '%invalid_code%' => 'rouge'
            ]
        )->willReturn($violationBuilder);
        $violationBuilder->atPath('[lace_color-<all_channels>-fr_FR]')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $context->buildViolation(
            $constraint->messagePlural,
            [
                '%attribute_code%' => 'fabrics',
                '%reference_data_name%' => 'fabrics',
                '%invalid_codes%' => 'leather, wool'
            ]
        )->willReturn($secondViolationBuilder);
        $secondViolationBuilder->atPath('[fabrics-ecommerce-<all_locales>]')->willReturn(
            $secondViolationBuilder
        );
        $secondViolationBuilder->addViolation()->shouldBecalled();

        $this->validate($values, $constraint);
    }

    function it_does_not_take_case_into_account_when_comparing_options(
        GetExistingReferenceDataCodes $getExistingReferenceDataCodes,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ExecutionContextInterface $context,
        AttributeInterface $laceColor,
        AttributeInterface $fabrics,
        ConstraintViolationBuilderInterface $violationBuilder,
        ConstraintViolationBuilderInterface $secondViolationBuilder
    ) {
        $laceColor->getReferenceDataName()->willReturn('color');
        $attributeRepository->findOneByIdentifier('lace_color')->willReturn($laceColor);
        $fabrics->getReferenceDataName()->willReturn('fabrics');
        $attributeRepository->findOneByIdentifier('fabrics')->willReturn($fabrics);

        $getExistingReferenceDataCodes->fromReferenceDataNameAndCodes('color', ['RED'])
                                      ->willReturn(['red']);
        $getExistingReferenceDataCodes->fromReferenceDataNameAndCodes('fabrics', ['cotton', 'WOOL', 'leATheR'])
                                      ->willReturn(['Cotton', 'Wool', 'Leather']);

        $context->buildViolation(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->validate(
            new WriteValueCollection(
                [
                    ReferenceDataValue::localizableValue('lace_color', 'RED', 'en_US'),
                    OptionsValue::value('fabrics', ['cotton', 'WOOL', 'leATheR']),
                ]
            ),
            new ReferenceDataOptionsExist()
        );
    }
}
