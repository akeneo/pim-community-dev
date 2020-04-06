<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\CalculateActionApplier;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyAdderInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CalculateActionApplierSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        PropertyAdderInterface $propertyAdder,
        GetAttributes $getAttributes
    )
    {
        $this->beConstructedWith($propertySetter, $propertyAdder, $getAttributes);
    }

    function it_is_an_action_applier()
    {
        $this->shouldImplement(ActionApplierInterface::class);
    }

    function it_is_a_calculate_action_aplier()
    {
        $this->shouldHaveType(CalculateActionApplier::class);
    }

    function it_only_supports_calculate_actions(
        ProductCalculateActionInterface $calculateAction,
        ActionInterface $otherAction
    ) {
        $this->supports($calculateAction)->shouldReturn(true);
        $this->supports($otherAction)->shouldReturn(false);
    }

    function it_does_nothing_on_products_without_family(PropertySetterInterface $propertySetter)
    {
        $product = new Product();
        $propertySetter->setData($product, Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($this->productCalculateAction(), [$product]);
    }

    function it_does_nothing_if_destination_field_does_not_belong_to_family(
        PropertySetterInterface $propertySetter
    ) {
        $family = new Family();
        $product = (new Product())->setFamily($family);

        $propertySetter->setData($product, Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($this->productCalculateAction(), [$product]);
    }

    function it_does_nothing_if_attribute_is_not_on_same_variation_level_as_entity(
        PropertySetterInterface $propertySetter,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        EntityWithFamilyVariantInterface $product
    ) {
        $family->hasAttributeCode('ratio_fr_en')->willReturn(true);
        $product->getFamily()->willReturn($family);
        $familyVariant->getLevelForAttributeCode('ratio_fr_en')->willReturn(1);
        $product->getFamilyVariant()->willReturn($familyVariant);
        $product->getVariationLevel()->willReturn(2);

        $propertySetter->setData($product, Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($this->productCalculateAction(), [$product]);
    }

    function it_skips_the_entity_if_one_of_the_operation_values_is_null(
        PropertySetterInterface $propertySetter,
        FamilyInterface $family
    ) {
        $family->hasAttributeCode('ratio_fr_en')->willReturn(true);
        $family->getId()->willReturn(42);
        $product = (new Product())->setFamily($family->getWrappedObject());
        $product->addValue(ScalarValue::localizableValue('total', 14.15, 'fr_FR'));

        $propertySetter->setData($product, Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($this->productCalculateAction(), [$product]);
    }

    function it_skips_the_entity_if_ther_is_a_division_by_zero_operation(
        PropertySetterInterface $propertySetter,
        FamilyInterface $family
    ) {
        $family->hasAttributeCode('ratio_fr_en')->willReturn(true);
        $family->getId()->willReturn(42);
        $product = (new Product())->setFamily($family->getWrappedObject());
        $product->addValue(ScalarValue::localizableValue('total', 5, 'fr_FR'));
        $product->addValue(ScalarValue::localizableValue('total', 0, 'en_US'));

        $propertySetter->setData($product, Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($this->productCalculateAction(), [$product]);
    }

    function it_calculates_attribute_values_for_a_number_destination(
        PropertySetterInterface $propertySetter,
        FamilyInterface $family,
        GetAttributes $getAttributes
    ) {
        $getAttributes->forCode('ratio_fr_en')->willReturn(new Attribute(
            'ratio_fr_en',
            AttributeTypes::NUMBER,
            [],
            false,
            false,
            null,
            true,
            'decimal',
            []
        ));

        $family->hasAttributeCode('ratio_fr_en')->willReturn(true);
        $family->getId()->willReturn(42);
        $product1 = (new Product())->setFamily($family->getWrappedObject());
        $product1->addValue(ScalarValue::localizableValue('total', 15, 'fr_FR'));
        $product1->addValue(ScalarValue::localizableValue('total', 50, 'en_US'));
        $product1->addValue(PriceCollectionValue::value(
            'base_price',
            new PriceCollection([new ProductPrice(20.35,'EUR'), new ProductPrice(25, 'USD')])
        ));

        $product2 = (new Product())->setFamily($family->getWrappedObject());
        $product2->addValue(ScalarValue::localizableValue('total', 15, 'fr_FR'));
        $product2->addValue(ScalarValue::localizableValue('total', 40, 'en_US'));
        $product2->addValue(PriceCollectionValue::value(
            'base_price',
            new PriceCollection([new ProductPrice(17.75, 'EUR')])
        ));

        $propertySetter->setData($product1, 'ratio_fr_en', 50.35, ['scope' => null, 'locale' => null])->shouldBeCalled();
        $propertySetter->setData($product2, 'ratio_fr_en', 55.25, ['scope' => null, 'locale' => null])->shouldBeCalled();

        $this->applyAction($this->productCalculateAction(), [$product1, $product2]);
    }

    function it_calculates_attribute_values_for_a_price_destination(
        PropertyAdderInterface $propertyAdder,
        FamilyInterface $family,
        GetAttributes $getAttributes
    ) {
        $getAttributes->forCode('ratio_fr_en')->willReturn(
            new Attribute(
                'ratio_fr_en',
                AttributeTypes::PRICE_COLLECTION,
                [],
                false,
                false,
                null,
                true,
                'prices',
                []
            )
        );

        $family->hasAttributeCode('ratio_fr_en')->willReturn(true);
        $family->getId()->willReturn(42);
        $product1 = (new Product())->setFamily($family->getWrappedObject());
        $product1->addValue(ScalarValue::localizableValue('total', 15, 'fr_FR'));
        $product1->addValue(ScalarValue::localizableValue('total', 50, 'en_US'));
        $product1->addValue(
            PriceCollectionValue::value(
                'base_price',
                new PriceCollection([new ProductPrice(20.35, 'EUR'), new ProductPrice(25, 'USD')])
            )
        );

        $product2 = (new Product())->setFamily($family->getWrappedObject());
        $product2->addValue(ScalarValue::localizableValue('total', 15, 'fr_FR'));
        $product2->addValue(ScalarValue::localizableValue('total', 40, 'en_US'));
        $product2->addValue(
            PriceCollectionValue::value(
                'base_price',
                new PriceCollection([new ProductPrice(17.75, 'EUR')])
            )
        );

        $propertyAdder->addData($product1, 'ratio_fr_en', [['amount' => 50.35, 'currency' => 'EUR']], ['scope' => null, 'locale' => null])->shouldBeCalled();
        $propertyAdder->addData($product2, 'ratio_fr_en', [['amount' => 55.25, 'currency' => 'EUR']], ['scope' => null, 'locale' => null])->shouldBeCalled();

        $this->applyAction($this->productCalculateAction(), [$product1, $product2]);
    }

    private function productCalculateAction(bool $destinationIsPrice = true): ProductCalculateActionInterface
    {
        $destination = [
            'field' => 'ratio_fr_en',
        ];
        if (true === $destinationIsPrice) {
            $destination['currency'] = 'EUR';
        }

        return new ProductCalculateAction(
            [
                'destination' => $destination,
                'source' => ['field' => 'total', 'locale' => 'fr_FR'],
                'operation_list' => [
                    [
                        'operator' => 'divide',
                        'field' => 'total',
                        'locale' => 'en_US',
                    ],
                    [
                        'operator' => 'multiply',
                        'value' => 100,
                    ],
                    [
                        'operator' => 'add',
                        'field' => 'base_price',
                        'currency' => 'EUR',
                    ],
                ],
            ]
        );
    }
}
