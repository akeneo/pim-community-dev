<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Calculate\GetOperandValue;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\CalculateActionApplier;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operand;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\PropertySetter;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CalculateActionApplierSpec extends ObjectBehavior
{
    function let(
        GetAttributes $getAttributes,
        GetOperandValue $getOperandValue,
        NormalizerInterface $priceNormalizer,
        PropertySetterInterface $propertySetter
    ) {
        $priceNormalizer->normalize(Argument::type(ProductPriceInterface::class), 'standard')
            ->will(function (array $arguments): array {
                $price = $arguments[0];

                return [
                    'amount' => $price->getData(),
                    'currency' => $price->getCurrency(),
                ];
            });
        $this->beConstructedWith($getAttributes, $getOperandValue, $priceNormalizer, $propertySetter);
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

    function it_does_nothing_on_products_without_family(
        GetAttributes $getAttributes,
        PropertySetter $propertySetter
    ) {
        $product = new Product();
        $getAttributes->forCode('ratio_fr_en')->willReturn($this->getDestinationAttribute());
        $propertySetter->setData($product, Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($this->productCalculateAction(), [$product])->shouldReturn([]);
    }

    function it_does_nothing_if_destination_field_does_not_belong_to_family(
        GetAttributes $getAttributes,
        PropertySetter $propertySetter
    ) {
        $family = new Family();
        $product = (new Product())->setFamily($family);
        $getAttributes->forCode('ratio_fr_en')->willReturn($this->getDestinationAttribute());

        $propertySetter->setData($product, Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($this->productCalculateAction(), [$product])->shouldReturn([]);
    }

    function it_does_nothing_if_attribute_is_not_on_same_variation_level_as_entity(
        GetAttributes $getAttributes,
        PropertySetter $propertySetter,
        FamilyInterface $family,
        FamilyVariantInterface $familyVariant,
        EntityWithFamilyVariantInterface $product
    ) {
        $getAttributes->forCode('ratio_fr_en')->willReturn($this->getDestinationAttribute());
        $family->hasAttributeCode('ratio_fr_en')->willReturn(false);
        $product->getFamily()->willReturn($family);

        $familyVariant->getLevelForAttributeCode('ratio_fr_en')->willReturn(1);
        $product->getFamilyVariant()->willReturn($familyVariant);
        $product->getVariationLevel()->willReturn(2);

        $propertySetter->setData($product, Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($this->productCalculateAction(), [$product])->shouldReturn([]);
    }

    function it_skips_the_entity_if_one_of_the_operation_values_is_null(
        GetAttributes $getAttributes,
        GetOperandValue $getOperandValue,
        PropertySetterInterface $propertySetter,
        FamilyInterface $family,
        EntityWithFamilyVariantInterface $product
    ) {
        $getAttributes->forCode('ratio_fr_en')->willReturn($this->getDestinationAttribute());
        $family->hasAttributeCode('ratio_fr_en')->willReturn(false);
        $product->getFamily()->willReturn($family);

        $action = $this->productCalculateAction();
        $getOperandValue->fromEntity($product, $action->getSource())->willReturn(null);

        $propertySetter->setData($product, Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$product])->shouldReturn([]);
    }

    function it_skips_the_entity_if_there_is_a_division_by_zero_operation(
        GetAttributes $getAttributes,
        GetOperandValue $getOperandValue,
        PropertySetterInterface $propertySetter,
        FamilyInterface $family,
        EntityWithFamilyVariantInterface $product
    ) {
        $getAttributes->forCode('ratio_fr_en')->willReturn($this->getDestinationAttribute());
        $family->hasAttributeCode('ratio_fr_en')->willReturn(true);
        $product->getFamily()->willReturn($family);
        $product->getFamilyVariant()->willReturn(null);

        $action = $this->productCalculateAction();
        $getOperandValue->fromEntity($product, $action->getSource())->willReturn(5.0);
        $divideOperation = $action->getOperationList()->getIterator()->getArrayCopy()[0];
        $getOperandValue->fromEntity($product, $divideOperation->getOperand())->willReturn(0.0);

        $propertySetter->setData($product, Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$product])->shouldReturn([]);
    }

    function it_calculates_a_number_value(
        GetAttributes $getAttributes,
        GetOperandValue $getOperandValue,
        PropertySetterInterface $propertySetter,
        FamilyInterface $family,
        EntityWithFamilyVariantInterface $product
    ) {
        $getAttributes->forCode('ratio_fr_en')->willReturn($this->getDestinationAttribute());
        $family->hasAttributeCode('ratio_fr_en')->willReturn(true);
        $product->getFamily()->willReturn($family);
        $product->getFamilyVariant()->willReturn(null);

        $action = $this->productCalculateAction();
        $getOperandValue->fromEntity($product, Argument::type(Operand::class))
            ->shouldBeCalledTimes(3)
            ->willReturn(5.0, 2.0, 10.0);

        $propertySetter->setData($product, 'ratio_fr_en', 260.0, ['scope' => null, 'locale' => null])->shouldBeCalled();

        $this->applyAction($action, [$product])->shouldReturn([$product]);
    }

    function it_calculates_a_metric_value_with_the_default_metric_unit(
        GetAttributes $getAttributes,
        GetOperandValue $getOperandValue,
        PropertySetterInterface $propertySetter,
        FamilyInterface $family,
        EntityWithFamilyVariantInterface $product
    ) {
        $getAttributes->forCode('ratio_fr_en')->willReturn($this->getDestinationAttribute('pim_catalog_metric', 'GRAM'));
        $family->hasAttributeCode('ratio_fr_en')->willReturn(true);
        $product->getFamily()->willReturn($family);
        $product->getFamilyVariant()->willReturn(null);

        $action = $this->productCalculateAction();
        $getOperandValue->fromEntity($product, Argument::type(Operand::class))
                        ->shouldBeCalledTimes(3)
                        ->willReturn(5.0, 2.0, 10.0);

        $propertySetter->setData(
            $product,
            'ratio_fr_en',
            ['amount' => 260.0, 'unit' => 'GRAM'],
            ['scope' => null, 'locale' => null]
        )->shouldBeCalled();

        $this->applyAction($action, [$product])->shouldReturn([$product]);
    }

    function it_calculates_a_metric_value_with_the_specified_unit(
        GetAttributes $getAttributes,
        GetOperandValue $getOperandValue,
        PropertySetterInterface $propertySetter,
        FamilyInterface $family,
        EntityWithFamilyVariantInterface $product
    ) {
        $getAttributes->forCode('ratio_fr_en')->willReturn(
            $this->getDestinationAttribute('pim_catalog_metric', 'GRAM')
        );
        $family->hasAttributeCode('ratio_fr_en')->willReturn(true);
        $product->getFamily()->willReturn($family);
        $product->getFamilyVariant()->willReturn(null);

        $action = $this->productCalculateAction(['unit' => 'KILOGRAM']);
        $getOperandValue->fromEntity($product, Argument::type(Operand::class))
                        ->shouldBeCalledTimes(3)
                        ->willReturn(5.0, 2.0, 10.0);

        $propertySetter->setData(
            $product,
            'ratio_fr_en',
            ['amount' => 260.0, 'unit' => 'KILOGRAM'],
            ['scope' => null, 'locale' => null]
        )->shouldBeCalled();

        $this->applyAction($action, [$product])->shouldReturn([$product]);
    }

    function it_calculates_a_new_price_collection_value(
        GetAttributes $getAttributes,
        GetOperandValue $getOperandValue,
        PropertySetterInterface $propertySetter,
        FamilyInterface $family,
        EntityWithFamilyVariantInterface $product
    ) {
        $getAttributes->forCode('ratio_fr_en')->willReturn(
            $this->getDestinationAttribute('pim_catalog_price_collection')
        );
        $family->hasAttributeCode('ratio_fr_en')->willReturn(true);
        $product->getFamily()->willReturn($family);
        $product->getFamilyVariant()->willReturn(null);
        $product->getValue('ratio_fr_en', null, null)->willReturn(null);

        $action = $this->productCalculateAction(['currency' => 'EUR']);
        $getOperandValue->fromEntity($product, Argument::type(Operand::class))
                        ->shouldBeCalledTimes(3)
                        ->willReturn(5.0, 2.0, 10.0);

        $propertySetter->setData(
            $product,
            'ratio_fr_en',
            [['amount' => 260.0, 'currency' => 'EUR']],
            ['scope' => null, 'locale' => null]
        )->shouldBeCalled();

        $this->applyAction($action, [$product])->shouldReturn([$product]);
    }

    function it_updates_an_existing_price_collection_value(
        GetAttributes $getAttributes,
        GetOperandValue $getOperandValue,
        PropertySetterInterface $propertySetter,
        FamilyInterface $family,
        EntityWithFamilyVariantInterface $product
    ) {
        $getAttributes->forCode('ratio_fr_en')->willReturn(
            $this->getDestinationAttribute('pim_catalog_price_collection')
        );
        $family->hasAttributeCode('ratio_fr_en')->willReturn(true);
        $product->getFamily()->willReturn($family);
        $product->getFamilyVariant()->willReturn(null);
        $product->getValue('ratio_fr_en', null, null)->willReturn(
            PriceCollectionValue::value('ratio_fr_en', new PriceCollection([
                new ProductPrice(119.99, 'USD'),
                new ProductPrice(109.99, 'EUR'),
            ]))
        );

        $action = $this->productCalculateAction(['currency' => 'EUR']);
        $getOperandValue->fromEntity($product, Argument::type(Operand::class))
                        ->shouldBeCalledTimes(3)
                        ->willReturn(5.0, 2.0, 10.0);

        $propertySetter->setData(
            $product,
            'ratio_fr_en',
            [
                ['amount' => 260.00, 'currency' => 'EUR'],
                ['amount' => 119.99, 'currency' => 'USD'],
            ],
            ['scope' => null, 'locale' => null]
        )->shouldBeCalled();

        $this->applyAction($action, [$product])->shouldReturn([$product]);
    }

    private function productCalculateAction(array $additionalOptions = []): ProductCalculateActionInterface
    {
        $destination = array_merge(['field' => 'ratio_fr_en'], $additionalOptions);
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

    private function getDestinationAttribute(string $type = 'pim_catalog_number', string $defaultMetricUnit = null): Attribute
    {
        return new Attribute(
            'ratio_fr_en',
            $type,
            [],
            false,
            false,
            $defaultMetricUnit ? 'MeasurementFamily' : null,
            $defaultMetricUnit,
            true,
            'backend_type',
            []
        );
    }
}
