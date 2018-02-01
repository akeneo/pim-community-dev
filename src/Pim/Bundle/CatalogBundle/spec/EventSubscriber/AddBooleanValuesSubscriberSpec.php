<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface;
use Pim\Component\Catalog\Manager\AttributeValuesResolverInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class AddBooleanValuesSubscriberSpec extends ObjectBehavior
{
    function let(
        AttributeValuesResolverInterface $valuesResolver,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder
    ) {
        $this->beConstructedWith($valuesResolver, $entityWithValuesBuilder);
    }

    function it_does_not_add_boolean_values_on_an_already_existing_product(
        $valuesResolver,
        $entityWithValuesBuilder,
        GenericEvent $event,
        Product $product
    ) {
        $event->getSubject()->willReturn($product);

        $product->getId()->willReturn(42);

        $valuesResolver->resolveEligibleValues(Argument::any())->shouldNotBeCalled();
        $entityWithValuesBuilder->addOrReplaceValue(Argument::cetera())->shouldNotBeCalled();

        $this->addBooleanToProduct($event);
    }

    function it_does_not_add_boolean_values_on_an_other_entity_than_a_product(
        $valuesResolver,
        $entityWithValuesBuilder,
        GenericEvent $event,
        \stdClass $object
    ) {
        $event->getSubject()->willReturn($object);

        $valuesResolver->resolveEligibleValues(Argument::any())->shouldNotBeCalled();
        $entityWithValuesBuilder->addOrReplaceValue(Argument::cetera())->shouldNotBeCalled();

        $this->addBooleanToProduct($event);
    }

    function it_adds_boolean_value_on_product_if_there_is_a_boolean_attribute_without_data(
        $valuesResolver,
        $entityWithValuesBuilder,
        GenericEvent $event,
        Product $product,
        FamilyInterface $family,
        Collection $attributeCollection,
        AttributeInterface $booleanAttribute,
        ValueInterface $originalValue
    ) {
        $event->getSubject()->willReturn($product);

        $product->getId()->willReturn(null);
        $product->getFamily()->willReturn($family);
        $family->getAttributes()->willReturn($attributeCollection);
        $attributeCollection->toArray()->willReturn([$booleanAttribute]);

        $booleanAttribute->getType()->willReturn(AttributeTypes::BOOLEAN);
        $booleanAttribute->getCode()->willReturn('a_boolean');
        $product->getValue('a_boolean', null, null)->willReturn($originalValue);
        $originalValue->getData()->willReturn(null);

        $valuesResolver->resolveEligibleValues([$booleanAttribute])->willReturn(['locale' => null, 'scope' => null]);
        $entityWithValuesBuilder->addOrReplaceValue($product, $booleanAttribute, null, null, false)->shouldBeCalled();

        $this->addBooleanToProduct($event);
    }

    function it_adds_boolean_value_on_product_if_there_is_a_boolean_attribute_without_value(
        $valuesResolver,
        $entityWithValuesBuilder,
        GenericEvent $event,
        Product $product,
        FamilyInterface $family,
        Collection $attributeCollection,
        AttributeInterface $booleanAttribute
    ) {
        $event->getSubject()->willReturn($product);

        $product->getId()->willReturn(null);
        $product->getFamily()->willReturn($family);
        $family->getAttributes()->willReturn($attributeCollection);
        $attributeCollection->toArray()->willReturn([$booleanAttribute]);

        $booleanAttribute->getType()->willReturn(AttributeTypes::BOOLEAN);
        $booleanAttribute->getCode()->willReturn('a_boolean');
        $product->getValue('a_boolean', null, null)->willReturn(null);

        $valuesResolver->resolveEligibleValues([$booleanAttribute])->willReturn(['locale' => null, 'scope' => null]);
        $entityWithValuesBuilder->addOrReplaceValue($product, $booleanAttribute, null, null, false)->shouldBeCalled();

        $this->addBooleanToProduct($event);
    }

    function it_does_not_add_boolean_value_on_product_if_there_is_a_boolean_attribute_with_data(
        $valuesResolver,
        $entityWithValuesBuilder,
        GenericEvent $event,
        Product $product,
        FamilyInterface $family,
        Collection $attributeCollection,
        AttributeInterface $booleanAttribute,
        ValueInterface $originalValue
    ) {
        $event->getSubject()->willReturn($product);

        $product->getId()->willReturn(null);
        $product->getFamily()->willReturn($family);
        $family->getAttributes()->willReturn($attributeCollection);
        $attributeCollection->toArray()->willReturn([$booleanAttribute]);

        $booleanAttribute->getType()->willReturn(AttributeTypes::BOOLEAN);
        $booleanAttribute->getCode()->willReturn('a_boolean');
        $product->getValue('a_boolean', null, null)->willReturn($originalValue);
        $originalValue->getData()->willReturn(false);

        $valuesResolver->resolveEligibleValues([$booleanAttribute])->willReturn(['locale' => null, 'scope' => null]);
        $entityWithValuesBuilder->addOrReplaceValue(Argument::cetera())->shouldNotBeCalled();

        $this->addBooleanToProduct($event);
    }

    function it_does_not_add_boolean_value_on_product_if_there_is_no_boolean_attribute_in_its_family(
        $valuesResolver,
        $entityWithValuesBuilder,
        GenericEvent $event,
        Product $product,
        FamilyInterface $family,
        Collection $attributeCollection,
        AttributeInterface $numberAttribute
    ) {
        $event->getSubject()->willReturn($product);

        $product->getId()->willReturn(null);
        $product->getFamily()->willReturn($family);
        $family->getAttributes()->willReturn($attributeCollection);
        $attributeCollection->toArray()->willReturn([$numberAttribute]);

        $numberAttribute->getType()->willReturn(AttributeTypes::NUMBER);

        $valuesResolver->resolveEligibleValues(Argument::any())->shouldNotBeCalled();
        $entityWithValuesBuilder->addOrReplaceValue(Argument::cetera())->shouldNotBeCalled();

        $this->addBooleanToProduct($event);
    }

    function it_does_not_add_boolean_values_on_product_if_it_has_no_family(
        $valuesResolver,
        $entityWithValuesBuilder,
        GenericEvent $event,
        Product $product
    ) {
        $event->getSubject()->willReturn($product);

        $product->getId()->willReturn(null);
        $product->getFamily()->willReturn(null);

        $valuesResolver->resolveEligibleValues(Argument::any())->shouldNotBeCalled();
        $entityWithValuesBuilder->addOrReplaceValue(Argument::cetera())->shouldNotBeCalled();

        $this->addBooleanToProduct($event);
    }

    function it_adds_boolean_value_on_variant_product_if_there_is_a_boolean_attribute_without_data(
        $valuesResolver,
        $entityWithValuesBuilder,
        GenericEvent $event,
        VariantProductInterface $product,
        FamilyVariantInterface $familyVariant,
        VariantAttributeSetInterface $attributeSet,
        Collection $attributeCollection,
        AttributeInterface $booleanAttribute,
        ValueInterface $originalValue
    ) {
        $event->getSubject()->willReturn($product);

        $product->getId()->willReturn(null);
        $product->getVariationLevel()->willReturn(1);
        $product->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSet(1)->willReturn($attributeSet);
        $attributeSet->getAttributes()->willReturn($attributeCollection);
        $attributeCollection->toArray()->willReturn([$booleanAttribute]);

        $booleanAttribute->getType()->willReturn(AttributeTypes::BOOLEAN);
        $booleanAttribute->getCode()->willReturn('a_boolean');
        $product->getValue('a_boolean', null, null)->willReturn($originalValue);
        $originalValue->getData()->willReturn(null);

        $valuesResolver->resolveEligibleValues([$booleanAttribute])->willReturn(['locale' => null, 'scope' => null]);
        $entityWithValuesBuilder->addOrReplaceValue($product, $booleanAttribute, null, null, false)->shouldBeCalled();

        $this->addBooleanToProduct($event);
    }

    function it_adds_boolean_value_on_variant_product_if_there_is_a_boolean_attribute_without_value(
        $valuesResolver,
        $entityWithValuesBuilder,
        GenericEvent $event,
        VariantProductInterface $product,
        FamilyVariantInterface $familyVariant,
        VariantAttributeSetInterface $attributeSet,
        Collection $attributeCollection,
        AttributeInterface $booleanAttribute
    ) {
        $event->getSubject()->willReturn($product);

        $product->getId()->willReturn(null);
        $product->getVariationLevel()->willReturn(1);
        $product->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSet(1)->willReturn($attributeSet);
        $attributeSet->getAttributes()->willReturn($attributeCollection);
        $attributeCollection->toArray()->willReturn([$booleanAttribute]);

        $booleanAttribute->getType()->willReturn(AttributeTypes::BOOLEAN);
        $booleanAttribute->getCode()->willReturn('a_boolean');
        $product->getValue('a_boolean', null, null)->willReturn(null);

        $valuesResolver->resolveEligibleValues([$booleanAttribute])->willReturn(['locale' => null, 'scope' => null]);
        $entityWithValuesBuilder->addOrReplaceValue($product, $booleanAttribute, null, null, false)->shouldBeCalled();

        $this->addBooleanToProduct($event);
    }

    function it_does_not_add_boolean_value_on_variant_product_if_there_is_a_boolean_attribute_with_data(
        $valuesResolver,
        $entityWithValuesBuilder,
        GenericEvent $event,
        VariantProductInterface $product,
        FamilyVariantInterface $familyVariant,
        VariantAttributeSetInterface $attributeSet,
        Collection $attributeCollection,
        AttributeInterface $booleanAttribute,
        ValueInterface $originalValue
    ) {
        $event->getSubject()->willReturn($product);

        $product->getId()->willReturn(null);
        $product->getVariationLevel()->willReturn(1);
        $product->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSet(1)->willReturn($attributeSet);
        $attributeSet->getAttributes()->willReturn($attributeCollection);
        $attributeCollection->toArray()->willReturn([$booleanAttribute]);

        $booleanAttribute->getType()->willReturn(AttributeTypes::BOOLEAN);
        $booleanAttribute->getCode()->willReturn('a_boolean');
        $product->getValue('a_boolean', null, null)->willReturn($originalValue);
        $originalValue->getData()->willReturn(false);

        $valuesResolver->resolveEligibleValues([$booleanAttribute])->willReturn(['locale' => null, 'scope' => null]);
        $entityWithValuesBuilder->addOrReplaceValue(Argument::cetera())->shouldNotBeCalled();

        $this->addBooleanToProduct($event);
    }

    function it_does_not_add_boolean_value_on_variant_product_if_there_is_no_boolean_attribute_in_its_family(
        $valuesResolver,
        $entityWithValuesBuilder,
        GenericEvent $event,
        VariantProductInterface $product,
        FamilyVariantInterface $familyVariant,
        VariantAttributeSetInterface $attributeSet,
        Collection $attributeCollection,
        AttributeInterface $numberAttribute
    ) {
        $event->getSubject()->willReturn($product);

        $product->getId()->willReturn(null);
        $product->getVariationLevel()->willReturn(1);
        $product->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSet(1)->willReturn($attributeSet);
        $attributeSet->getAttributes()->willReturn($attributeCollection);
        $attributeCollection->toArray()->willReturn([$numberAttribute]);

        $numberAttribute->getType()->willReturn(AttributeTypes::NUMBER);

        $valuesResolver->resolveEligibleValues(Argument::any())->shouldNotBeCalled();
        $entityWithValuesBuilder->addOrReplaceValue(Argument::cetera())->shouldNotBeCalled();

        $this->addBooleanToProduct($event);
    }
}
