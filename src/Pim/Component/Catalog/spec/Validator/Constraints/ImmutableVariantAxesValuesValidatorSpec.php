<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Factory\ValueCollectionFactoryInterface;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Validator\Constraints\ImmutableVariantAxesValues;
use Pim\Component\Catalog\Validator\Constraints\ImmutableVariantAxesValuesValidator;
use Pim\Component\Catalog\Validator\Constraints\VariantProductParent;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ImmutableVariantAxesValuesValidatorSpec extends ObjectBehavior
{
    function let(
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        ValueCollectionFactoryInterface $valueCollectionFactory,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($attributesProvider, $valueCollectionFactory);

        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ImmutableVariantAxesValuesValidator::class);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldBeAnInstanceOf(ConstraintValidator::class);
    }

    function it_throws_an_exception_if_it_does_not_validate_an_entity_with_variant(
        UserInterface $entity,
        ImmutableVariantAxesValues $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [
            $entity,
            $constraint
        ]);
    }

    function it_throws_an_exception_if_it_does_not_validate_against_the_correct_constraint(
        EntityWithFamilyVariantInterface $entity,
        VariantProductParent $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [
            $entity,
            $constraint
        ]);
    }

    function it_does_not_build_a_violation_if_the_entity_has_no_id(
        $context,
        ProductInterface $variantProduct,
        ImmutableVariantAxesValues $constraint
    ) {
        $variantProduct->getId()->willReturn(null);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($variantProduct, $constraint);
    }

    function it_does_not_build_a_violation_if_the_entity_has_no_familyVariant(
        $context,
        ProductInterface $variantProduct,
        ImmutableVariantAxesValues $constraint
    ) {
        $variantProduct->getId()->willReturn(42);
        $variantProduct->getFamilyVariant()->willReturn(null);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($variantProduct, $constraint);
    }

    function it_adds_a_violation_if_the_variant_axis_values_are_updated(
        $context,
        $attributesProvider,
        $valueCollectionFactory,
        ProductInterface $variantProduct,
        ImmutableVariantAxesValues $constraint,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $sizeAttribute,
        AttributeInterface $colorAttribute,
        UnitOfWork $uow,
        ValueCollectionInterface $originalValues,
        ValueInterface $originalSizeValue,
        ValueInterface $originalColorValue,
        ValueInterface $newSizeValue,
        ValueInterface $newColorValue,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $originalRawData = [
            'size' => [
                '<all_channels>' => [
                    '<all_locales>' => 'xl',
                ],
            ],
            'color' => [
                '<all_channels>' => [
                    '<all_locales>' => 'red',
                ],
            ],
        ];

        $variantProduct->getId()->willReturn(42);
        $variantProduct->getFamilyVariant()->willReturn($familyVariant);

        $attributesProvider->getAxes($variantProduct)->willReturn([$sizeAttribute, $colorAttribute]);
        $sizeAttribute->getCode()->willReturn('size');
        $colorAttribute->getCode()->willReturn('color');

        $variantProduct->getRawValues()->willReturn($originalRawData);
        $valueCollectionFactory->createFromStorageFormat($originalRawData)->willReturn($originalValues);

        $originalValues->getByCodes('size')->willReturn($originalSizeValue);
        $originalValues->getByCodes('color')->willReturn($originalColorValue);
        $variantProduct->getValue('size')->willReturn($newSizeValue);
        $variantProduct->getValue('color')->willReturn($newColorValue);

        $newSizeValue->getData()->willReturn('[m]');
        $newColorValue->getData()->willReturn('[blue]');
        $newSizeValue->__toString()->willReturn('[m]');
        $newColorValue->__toString()->willReturn('[blue]');

        $originalSizeValue->isEqual($newSizeValue)->willReturn(false);
        $originalColorValue->isEqual($newColorValue)->willReturn(false);

        $context->buildViolation(ImmutableVariantAxesValues::UPDATED_VARIANT_AXIS_VALUE, [
            '%variant_axis%' => 'size',
            '%provided_value%' => '[m]',
        ])->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('attribute')->willReturn($constraintViolationBuilder);
        $context->buildViolation(ImmutableVariantAxesValues::UPDATED_VARIANT_AXIS_VALUE, [
            '%variant_axis%' => 'color',
            '%provided_value%' => '[blue]',
        ])->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledTimes(2);

        $this->validate($variantProduct, $constraint);
    }
}
