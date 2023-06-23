<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetValuesOfSiblings;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueVariantAxis;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueVariantAxisValidator;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueAxesCombinationSet;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueVariantAxisValidatorSpec extends ObjectBehavior
{
    function let(
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        GetValuesOfSiblings $getValuesOfSiblings,
        ExecutionContextInterface $context,
        ConstraintViolationListInterface $constraintViolationList
    ) {
        $context->getViolations()->willReturn($constraintViolationList);
        $constraintViolationList->count()->willReturn(0);

        $this->beConstructedWith($axesProvider, new UniqueAxesCombinationSet(), $getValuesOfSiblings);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueVariantAxisValidator::class);
    }

    function it_throws_an_exception_if_the_entity_is_not_supported(
        \DateTime $entity,
        UniqueVariantAxis $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$entity, $constraint]);
    }

    function it_throws_an_exception_if_the_constraint_is_not_supported(
        Constraint $constraint
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [new Product(), $constraint]);
    }

    function it_raises_no_violation_if_the_entity_has_no_family_variant(
        ExecutionContextInterface $context,
        UniqueVariantAxis $constraint
    ) {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new Product(), $constraint);
    }

    function it_raises_no_violation_if_the_entity_has_no_parent(
        ExecutionContextInterface $context,
        UniqueVariantAxis $constraint
    ) {
        $product = new Product();
        $product->setFamilyVariant(new FamilyVariant());

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($product, $constraint);
    }

    function it_raises_no_violation_if_the_entity_has_no_axis(
        ExecutionContextInterface $context,
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        EntityWithFamilyVariantInterface $entity,
        UniqueVariantAxis $constraint
    ) {
        $entity->getParent()->willReturn($parent);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $axesProvider->getAxes($entity)->willReturn([]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_the_entity_has_no_sibling(
        ExecutionContextInterface $context,
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        GetValuesOfSiblings $getValuesOfSiblings,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        EntityWithFamilyVariantInterface $entity,
        UniqueVariantAxis $constraint
    ) {
        $axes = [];
        $entity->getParent()->willReturn($parent);
        $axesProvider->getAxes($entity)->willReturn($axes);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $getValuesOfSiblings->for($entity, [])->willReturn([]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_axes_combination_is_empty(
        ExecutionContextInterface $context,
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        ProductModelInterface $entity,
        AttributeInterface $color,
        WriteValueCollection $values,
        ValueInterface $emptyValue,
        UniqueVariantAxis $constraint
    ) {
        $entity->getParent()->willReturn($parent);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $entity->getValuesForVariation()->willReturn($values);
        $values->getByCodes('color')->willReturn($emptyValue);
        $axesProvider->getAxes($entity)->willReturn([$color]);
        $color->getCode()->willReturn('color');

        $entity->getValue('color')->willReturn($emptyValue);
        $emptyValue->__toString()->willReturn('');

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_there_is_no_duplicate_in_any_sibling_product_model_from_database(
        ExecutionContextInterface $context,
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        GetValuesOfSiblings $getValuesOfSiblings,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        ProductModelInterface $entity,
        AttributeInterface $color,
        WriteValueCollection $values,
        WriteValueCollection $valuesOfFirstSibling,
        WriteValueCollection $valuesOfSecondSibling,
        ValueInterface $blue,
        ValueInterface $red,
        ValueInterface $yellow,
        UniqueVariantAxis $constraint
    ) {
        $axes = [$color];

        $entity->getIdentifier()->willReturn('toto');
        $entity->getParent()->willReturn($parent);
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $axesProvider->getAxes($entity)->willReturn($axes);
        $color->getCode()->willReturn('color');

        $values->getByCodes('color')->willReturn($blue);
        $entity->getValuesForVariation()->willReturn($values);

        $blue->__toString()->willReturn('[blue]');
        $red->__toString()->willReturn('[red]');
        $yellow->__toString()->willReturn('[yellow]');

        $getValuesOfSiblings->for($entity, ['color'])->willReturn([
            'sibling1' => $valuesOfFirstSibling,
            'sibling2' => $valuesOfSecondSibling,
        ]);
        $valuesOfFirstSibling->getByCodes('color')->willReturn($red);
        $valuesOfSecondSibling->getByCodes('color')->willReturn($yellow);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_there_is_no_duplicate_in_any_sibling_variant_product_from_database(
        ExecutionContextInterface $context,
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        GetValuesOfSiblings $getValuesOfSiblings,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        AttributeInterface $color,
        WriteValueCollection $valuesOfSibling,
        UniqueVariantAxis $constraint
    ) {
        $axes = [$color];

        $entity = new Product();
        $entity->setParent($parent->getWrappedObject());
        $entity->setFamilyVariant($familyVariant->getWrappedObject());
        $entity->addValue(OptionValue::value('color', 'blue'));

        $axesProvider->getAxes($entity)->willReturn($axes);
        $color->getCode()->willReturn('color');

        $getValuesOfSiblings->for($entity, ['color'])->willReturn(
            [
                'sibbling_identifier' => $valuesOfSibling,
            ]
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_no_violation_if_there_is_no_duplicate_in_any_sibling_variant_with_similar_values(
        ExecutionContextInterface $context,
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        GetValuesOfSiblings $getValuesOfSiblings,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        AttributeInterface $brand,
        UniqueVariantAxis $constraint
    ) {
        $brand->getCode()->willReturn('brand');
        $axes = [$brand];

        $entity = new Product();
        $entity->setParent($parent->getWrappedObject());
        $entity->setFamilyVariant($familyVariant->getWrappedObject());
        $entity->addValue(OptionValue::value('brand', '01'));

        $axesProvider->getAxes($entity)->willReturn($axes);

        $getValuesOfSiblings->for($entity, ['brand'])->willReturn(
            [
                'sibling_identifier' => new WriteValueCollection([OptionValue::value('brand', '1')]),
            ]
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicate_value_in_any_sibling_product_model(
        ExecutionContextInterface $context,
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        GetValuesOfSiblings $getValuesOfSiblings,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        AttributeInterface $color,
        UniqueVariantAxis $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $color->getCode()->willReturn('color');
        $axes = [$color];

        $entity = new ProductModel();
        $entity->setCode('entity_code');
        $entity->setParent($parent->getWrappedObject());
        $entity->setFamilyVariant($familyVariant->getWrappedObject());
        $entity->addValue(OptionValue::value('color', 'blue'));

        $axesProvider->getAxes($entity)->willReturn($axes);

        $getValuesOfSiblings->for($entity, ['color'])->willReturn(
            [
                'sibling1' => new WriteValueCollection([OptionValue::value('color', 'yellow')]),
                'sibling2' => new WriteValueCollection([OptionValue::value('color', 'blue')]),
            ]
        );

        $context
            ->buildViolation(
                UniqueVariantAxis::DUPLICATE_VALUE_IN_PRODUCT_MODEL,
                [
                    '%values%' => '[blue]',
                    '%attributes%' => 'color',
                    '%validated_entity%' => 'entity_code',
                    '%sibling_with_same_value%' => 'sibling2',
                ]
            )
            ->willReturn($violation);
        $violation->atPath('attribute')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicate_value_in_any_sibling_variant_product(
        ExecutionContextInterface $context,
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        GetValuesOfSiblings $getValuesOfSiblings,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        AttributeInterface $color,
        UniqueVariantAxis $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $color->getCode()->willReturn('color');
        $axes = [$color];

        $entity = new Product();
        $entity->setIdentifier('my_identifier');
        $entity->setParent($parent->getWrappedObject());
        $entity->setFamilyVariant($familyVariant->getWrappedObject());
        $entity->addValue(OptionValue::value('color', 'blue'));

        $axesProvider->getAxes($entity)->willReturn($axes);

        $getValuesOfSiblings->for($entity, ['color'])->willReturn(
            [
                'sibling1' => new WriteValueCollection([OptionValue::value('color', 'yellow')]),
                'sibling2' => new WriteValueCollection([OptionValue::value('color', 'blue')]),
            ]
        );

        $context
            ->buildViolation(
                UniqueVariantAxis::DUPLICATE_VALUE_IN_VARIANT_PRODUCT,
                [
                    '%values%' => '[blue]',
                    '%attributes%' => 'color',
                    '%validated_entity%' => 'my_identifier',
                    '%sibling_with_same_value%' => 'sibling2',
                ]
            )
            ->willReturn($violation);
        $violation->atPath('attribute')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicate_boolean_value_in_any_sibling_variant_product(
        ExecutionContextInterface $context,
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        GetValuesOfSiblings $getValuesOfSiblings,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        AttributeInterface $autoExposure,
        UniqueVariantAxis $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $autoExposure->getCode()->willReturn('auto_exposure');
        $axes = [$autoExposure];

        $entity = new Product();
        $entity->setIdentifier('my_identifier');
        $entity->setParent($parent->getWrappedObject());
        $entity->setFamilyVariant($familyVariant->getWrappedObject());
        $entity->addValue(ScalarValue::value('auto_exposure', false));

        $axesProvider->getAxes($entity)->willReturn($axes);

        $getValuesOfSiblings->for($entity, ['auto_exposure'])->willReturn(
            [
                'sibling1' => new WriteValueCollection([ScalarValue::value('auto_exposure', null)]),
                'sibling2' => new WriteValueCollection([ScalarValue::value('auto_exposure', false)]),
            ]
        );

        $context
            ->buildViolation(
                UniqueVariantAxis::DUPLICATE_VALUE_IN_VARIANT_PRODUCT,
                [
                    '%values%' => '0',
                    '%attributes%' => 'auto_exposure',
                    '%validated_entity%' => 'my_identifier',
                    '%sibling_with_same_value%' => 'sibling2',
                ]
            )
            ->willReturn($violation);
        $violation->atPath('attribute')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicate_value_with_a_different_case_in_any_sibling_product_model(
        ExecutionContextInterface $context,
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        GetValuesOfSiblings $getValuesOfSiblings,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        AttributeInterface $color,
        UniqueVariantAxis $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $color->getCode()->willReturn('color');
        $axes = [$color];

        $entity = new Product();
        $entity->setIdentifier('my_identifier');
        $entity->setParent($parent->getWrappedObject());
        $entity->setFamilyVariant($familyVariant->getWrappedObject());
        $entity->addValue(OptionValue::value('color', 'Blue'));

        $axesProvider->getAxes($entity)->willReturn($axes);

        $getValuesOfSiblings->for($entity, ['color'])->willReturn(
            [
                'sibling1' => new WriteValueCollection([OptionValue::value('color', 'yellow')]),
                'sibling2' => new WriteValueCollection([OptionValue::value('color', 'blue')]),
            ]
        );

        $context
            ->buildViolation(
                UniqueVariantAxis::DUPLICATE_VALUE_IN_VARIANT_PRODUCT,
                [
                    '%values%' => '[Blue]',
                    '%attributes%' => 'color',
                    '%validated_entity%' => 'my_identifier',
                    '%sibling_with_same_value%' => 'sibling2',
                ]
            )
            ->willReturn($violation);
        $violation->atPath('attribute')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicate_value_in_any_sibling_variant_product_with_multiple_attributes_in_axis(
        ExecutionContextInterface $context,
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        GetValuesOfSiblings $getValuesOfSiblings,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        AttributeInterface $color,
        AttributeInterface $size,
        UniqueVariantAxis $constraint,
        ConstraintViolationBuilderInterface $violation,
    ) {
        $color->getCode()->willReturn('color');
        $size->getCode()->willReturn('size');
        $axes = [$color, $size];

        $entity = new Product();
        $entity->setIdentifier('entity_code');
        $entity->setParent($parent->getWrappedObject());
        $entity->setFamilyVariant($familyVariant->getWrappedObject());
        $entity->addValue(OptionValue::value('color', 'blue'));
        $entity->addValue(OptionValue::value('size', 'xl'));

        $axesProvider->getAxes($entity)->willReturn($axes);

        $getValuesOfSiblings->for($entity, ['color', 'size'])->willReturn(
            [
                'sibling1' => new WriteValueCollection([
                    OptionValue::value('color', 'yellow'),
                    OptionValue::value('size', 'xl'),
                ]),
                'sibling2' => new WriteValueCollection([
                    OptionValue::value('color', 'blue'),
                    OptionValue::value('size', 'xl'),
                ]),
            ]
        );

        $context
            ->buildViolation(
                UniqueVariantAxis::DUPLICATE_VALUE_IN_VARIANT_PRODUCT,
                [
                    '%values%' => '[blue],[xl]',
                    '%attributes%' => 'color,size',
                    '%validated_entity%' => 'entity_code',
                    '%sibling_with_same_value%' => 'sibling2',
                ]
            )
            ->willReturn($violation);
        $violation->atPath('attribute')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicated_product_model_in_the_batch(
        ExecutionContextInterface $context,
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        GetValuesOfSiblings $getValuesOfSiblings,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        AttributeInterface $color,
        UniqueVariantAxis $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $color->getCode()->willReturn('color');

        $entity1 = new ProductModel();
        $entity1->setCode('entity_1');
        $entity1->setParent($parent->getWrappedObject());
        $entity1->setFamilyVariant($familyVariant->getWrappedObject());
        $entity1->addValue(OptionValue::value('color', 'blue'));

        $entity2 = new ProductModel();
        $entity2->setCode('entity_2');
        $entity2->setParent($parent->getWrappedObject());
        $entity2->setFamilyVariant($familyVariant->getWrappedObject());
        $entity2->addValue(OptionValue::value('color', 'blue'));

        $getValuesOfSiblings->for($entity1, ['color'])->shouldBeCalled()->willReturn([]);
        $getValuesOfSiblings->for($entity2, ['color'])->willReturn([]);

        $axesProvider->getAxes($entity1)->willReturn([$color]);
        $axesProvider->getAxes($entity2)->willReturn([$color]);

        $context
            ->buildViolation(
                UniqueVariantAxis::DUPLICATE_VALUE_IN_PRODUCT_MODEL,
                [
                    '%values%' => '[blue]',
                    '%attributes%' => 'color',
                    '%validated_entity%' => 'entity_2',
                    '%sibling_with_same_value%' => 'entity_1',
                ]
            )
            ->willReturn($violation);
        $violation->atPath('attribute')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity1, $constraint);
        $this->validate($entity2, $constraint);
    }

    function it_raises_a_violation_if_there_is_a_duplicated_variant_product_in_the_batch(
        ExecutionContextInterface $context,
        EntityWithFamilyVariantAttributesProvider $axesProvider,
        GetValuesOfSiblings $getValuesOfSiblings,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        AttributeInterface $color,
        UniqueVariantAxis $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $color->getCode()->willReturn('color');

        $entity1 = new Product();
        $entity1->setIdentifier('entity_1');
        $entity1->setParent($parent->getWrappedObject());
        $entity1->setFamilyVariant($familyVariant->getWrappedObject());
        $entity1->addValue(OptionValue::value('color', 'blue'));

        $entity2 = new Product();
        $entity2->setIdentifier('entity_2');
        $entity2->setParent($parent->getWrappedObject());
        $entity2->setFamilyVariant($familyVariant->getWrappedObject());
        $entity2->addValue(OptionValue::value('color', 'blue'));

        $getValuesOfSiblings->for($entity1, ['color'])->shouldBeCalled()->willReturn([]);
        $getValuesOfSiblings->for($entity2, ['color'])->willReturn([]);

        $axesProvider->getAxes($entity1)->willReturn([$color]);
        $axesProvider->getAxes($entity2)->willReturn([$color]);

        $context
            ->buildViolation(
                UniqueVariantAxis::DUPLICATE_VALUE_IN_VARIANT_PRODUCT,
                [
                    '%values%' => '[blue]',
                    '%attributes%' => 'color',
                    '%validated_entity%' => 'entity_2',
                    '%sibling_with_same_value%' => 'entity_1',
                ]
            )
            ->willReturn($violation);
        $violation->atPath('attribute')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($entity1, $constraint);
        $this->validate($entity2, $constraint);
    }
}
