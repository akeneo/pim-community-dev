<?php

namespace spec\Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\AddBooleanValuesToNewProductSubscriber;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AddBooleanValuesToNewProductSubscriberSpec extends ObjectBehavior
{
    function let(AttributeValuesResolverInterface $valuesResolver, ValueFactory $productValueFactory)
    {
        $this->beConstructedWith($valuesResolver,  $productValueFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddBooleanValuesToNewProductSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_does_nothing_if_the_subject_of_the_event_is_not_a_product(
        GenericEvent $event,
        AttributeInterface $attribute
    ) {
        $event->getSubject()->willReturn($attribute);

        $this->addBooleansDefaultValues($event);
    }

    function it_does_nothing_if_the_product_already_exist(
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(42);

        $product->addValue(Argument::any())->shouldNotBeCalled();

        $this->addBooleansDefaultValues($event);
    }

    function it_adds_default_boolean_attribute_values_to_a_new_product(
        $valuesResolver,
        $productValueFactory,
        GenericEvent $event,
        ProductInterface $product,
        FamilyInterface $family,
        AttributeInterface $textAttribute,
        AttributeInterface $booleanAttribute,
        ValueInterface $valueEn,
        ValueInterface $valueFr
    ) {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(null);
        $product->getFamily()->willReturn($family);
        $product->isVariant()->willReturn(false);
        $family->getAttributes()->willReturn([$textAttribute, $booleanAttribute]);
        $textAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $booleanAttribute->getType()->willReturn(AttributeTypes::BOOLEAN);
        $booleanAttribute->getCode()->willReturn('is_boolean');

        $valuesResolver->resolveEligibleValues([$booleanAttribute])->willReturn([
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
            ],
            [
                'locale' => 'fr_FR',
                'scope'  => 'ecommerce',
            ]
        ]);

        $product->getValue('is_boolean', 'en_US', 'ecommerce')->willReturn(null);
        $product->getValue('is_boolean', 'fr_FR', 'ecommerce')->willReturn(null);

        $productValueFactory->create($booleanAttribute, 'ecommerce', 'en_US', false)->willReturn($valueEn);
        $productValueFactory->create($booleanAttribute, 'ecommerce', 'fr_FR', false)->willReturn($valueFr);

        $product->addValue($valueEn)->shouldBeCalled();
        $product->addValue($valueFr)->shouldBeCalled();

        $this->addBooleansDefaultValues($event);
    }

    function it_does_not_replace_boolean_attribute_values_to_a_new_product(
        $valuesResolver,
        GenericEvent $event,
        ProductInterface $product,
        FamilyInterface $family,
        AttributeInterface $booleanAttribute,
        ValueInterface $value
    ) {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(null);
        $product->getFamily()->willReturn($family);
        $product->isVariant()->willReturn(false);
        $family->getAttributes()->willReturn([$booleanAttribute]);
        $booleanAttribute->getType()->willReturn(AttributeTypes::BOOLEAN);
        $booleanAttribute->getCode()->willReturn('is_boolean');

        $valuesResolver->resolveEligibleValues([$booleanAttribute])->willReturn([
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
            ]
        ]);

        $product->getValue('is_boolean', 'en_US', 'ecommerce')->willReturn($value);

        $product->addValue(Argument::any())->shouldNotBeCalled();

        $this->addBooleansDefaultValues($event);
    }

    function it_adds_only_the_boolean_attributes_of_the_variant_family_to_a_new_variant_product(
        $valuesResolver,
        $productValueFactory,
        GenericEvent $event,
        ProductInterface $variantProduct,
        ProductModelInterface $parentProduct,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $booleanAttribute,
        VariantAttributeSetInterface $variantAttributeSet,
        ValueInterface $value
    ) {
        $event->getSubject()->willReturn($variantProduct);
        $variantProduct->getId()->willReturn(null);
        $variantProduct->getParent()->willReturn($parentProduct);
        $variantProduct->getVariationLevel()->willReturn(1);
        $variantProduct->isVariant()->willReturn(true);
        $parentProduct->getFamilyVariant()->willReturn($familyVariant);

        $booleanAttribute->getType()->willReturn(AttributeTypes::BOOLEAN);
        $booleanAttribute->getCode()->willReturn('is_boolean');

        $familyVariant->getVariantAttributeSet(1)->willReturn($variantAttributeSet);
        $variantAttributeSet->getAttributes()->willReturn(new ArrayCollection([$booleanAttribute->getWrappedObject()]));
        $familyVariant->getAxes()->willReturn(new ArrayCollection([]));

        $valuesResolver->resolveEligibleValues([$booleanAttribute])->willReturn([
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
            ]
        ]);

        $variantProduct->getValue('is_boolean', 'en_US', 'ecommerce')->willReturn(null);
        $productValueFactory->create($booleanAttribute, 'ecommerce', 'en_US', false)->willReturn($value);

        $variantProduct->addValue($value)->shouldBeCalled();

        $this->addBooleansDefaultValues($event);
    }

    function it_does_not_adds_boolean_attributes_defined_as_axis(
        $valuesResolver,
        $productValueFactory,
        GenericEvent $event,
        ProductInterface $variantProduct,
        ProductModelInterface $parentProduct,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $booleanAttribute,
        AttributeInterface $booleanAxis,
        VariantAttributeSetInterface $variantAttributeSet,
        ValueInterface $value
    ) {
        $event->getSubject()->willReturn($variantProduct);
        $variantProduct->getId()->willReturn(null);
        $variantProduct->getParent()->willReturn($parentProduct);
        $variantProduct->getVariationLevel()->willReturn(1);
        $variantProduct->isVariant()->willReturn(true);
        $parentProduct->getFamilyVariant()->willReturn($familyVariant);

        $booleanAttribute->getType()->willReturn(AttributeTypes::BOOLEAN);
        $booleanAttribute->getCode()->willReturn('is_boolean');

        $booleanAxis->getType()->willReturn(AttributeTypes::BOOLEAN);

        $familyVariant->getVariantAttributeSet(1)->willReturn($variantAttributeSet);
        $variantAttributeSet->getAttributes()->willReturn(new ArrayCollection([
            $booleanAttribute->getWrappedObject(),
            $booleanAxis->getWrappedObject(),
        ]));
        $familyVariant->getAxes()->willReturn(new ArrayCollection([$booleanAxis->getWrappedObject()]));

        $valuesResolver->resolveEligibleValues([$booleanAttribute])->willReturn([
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
            ]
        ]);

        $variantProduct->getValue('is_boolean', 'en_US', 'ecommerce')->willReturn(null);
        $productValueFactory->create($booleanAttribute, 'ecommerce', 'en_US', false)->willReturn($value);

        $variantProduct->addValue($value)->shouldBeCalled();

        $this->addBooleansDefaultValues($event);
    }
}
