<?php

namespace spec\Pim\Component\Catalog\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Manager\AttributeValuesResolver;
use Pim\Component\Catalog\Model\Association;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\ProductValue\ScalarProductValue;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\ProductEvents;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Prophecy\Argument;
use Prophecy\Exception\Prediction\FailedPredictionException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductBuilderSpec extends ObjectBehavior
{
    const PRODUCT_CLASS = Product::class;
    const ASSOCIATION_CLASS = Association::class;

    function let(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        CurrencyRepositoryInterface $currencyRepository,
        AssociationTypeRepositoryInterface $assocTypeRepository,
        EventDispatcherInterface $eventDispatcher,
        AttributeValuesResolver $valuesResolver,
        ProductValueFactory $productValueFactory
    ) {
        $entityConfig = [
            'product' => self::PRODUCT_CLASS,
            'association' => self::ASSOCIATION_CLASS,
        ];

        $this->beConstructedWith(
            $attributeRepository,
            $familyRepository,
            $currencyRepository,
            $assocTypeRepository,
            $eventDispatcher,
            $valuesResolver,
            $productValueFactory,
            $entityConfig
        );
    }

    function it_creates_product_without_family($eventDispatcher)
    {
        $eventDispatcher->dispatch(ProductEvents::CREATE, Argument::any())->shouldBeCalled();

        $this->createProduct()->shouldReturnAnInstanceOf(self::PRODUCT_CLASS);
    }

    function it_creates_product_with_a_family_and_an_identifier(
        $familyRepository,
        $attributeRepository,
        $eventDispatcher,
        $productValueFactory,
        FamilyInterface $tshirtFamily,
        AttributeInterface $identifierAttribute,
        ProductValueInterface $identifierValue
    ) {
        $eventDispatcher->dispatch(ProductEvents::CREATE, Argument::any())->shouldBeCalled();

        $familyRepository->findOneByIdentifier("tshirt")->willReturn($tshirtFamily);
        $tshirtFamily->getId()->shouldBeCalled();
        $tshirtFamily->getAttributes()->willReturn([]);

        $identifierAttribute->isUnique()->willReturn(false);
        $attributeRepository->getIdentifier()->willReturn($identifierAttribute);
        $identifierAttribute->getCode()->willReturn('sku');
        $identifierAttribute->getType()->willReturn(AttributeTypes::IDENTIFIER);

        $productValueFactory->create($identifierAttribute, null, null, 'mysku')->willReturn($identifierValue);
        $identifierValue->getData()->willReturn('mysku');
        $identifierValue->getAttribute()->willReturn($identifierAttribute);
        $identifierValue->getLocale()->willReturn(null);
        $identifierValue->getScope()->willReturn(null);

        $product = $this->createProduct('mysku', 'tshirt')->shouldReturnAnInstanceOf(self::PRODUCT_CLASS);

        if ('mysku' !== $product->getIdentifier()) {
            throw new FailedPredictionException('Expecting "mysku" as identifier for the product.');
        }
    }

    function it_adds_missing_product_values_from_family_on_new_product(
        $valuesResolver,
        $productValueFactory,
        FamilyInterface $family,
        ProductInterface $product,
        AttributeInterface $sku,
        AttributeInterface $name,
        AttributeInterface $desc,
        ProductValueInterface $skuValue
    ) {
        $valueClass = ScalarProductValue::class;
        $attributeClass = Attribute::class;

        $sku->getCode()->willReturn('sku');
        $sku->getType()->willReturn('pim_catalog_identifier');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $name->getCode()->willReturn('name');
        $name->getType()->willReturn('pim_catalog_text');
        $name->isLocalizable()->willReturn(true);
        $name->isScopable()->willReturn(false);

        $desc->getCode()->willReturn('description');
        $desc->getType()->willReturn('pim_catalog_text');
        $desc->isLocalizable()->willReturn(true);
        $desc->isScopable()->willReturn(true);

        // get expected attributes
        $product->getAttributes()->willReturn([$sku]);
        $family->getAttributes()->willReturn([$sku, $name, $desc]);
        $product->getFamily()->willReturn($family);

        // get eligible values
        $valuesResolver->resolveEligibleValues(['sku' => $sku, 'name' => $name, 'description' => $desc], null, null)
            ->willReturn([
                [
                    'attribute' => 'sku',
                    'type' => 'pim_catalog_identifier',
                    'locale' => null,
                    'scope' => null
                ],
                [
                    'attribute' => 'name',
                    'type' => 'pim_catalog_text',
                    'locale' => 'fr_FR',
                    'scope' => null
                ],
                [
                    'attribute' => 'name',
                    'type' => 'pim_catalog_text',
                    'locale' => 'en_US',
                    'scope' => null
                ],
                [
                    'attribute' => 'description',
                    'type' => 'pim_catalog_text',
                    'locale' => 'en_US',
                    'scope' => 'ecommerce'
                ],
                [
                    'attribute' => 'description',
                    'type' => 'pim_catalog_text',
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce'
                ],
                [
                    'attribute' => 'description',
                    'type' => 'pim_catalog_text',
                    'locale' => 'en_US',
                    'scope' => 'print'
                ],
                [
                    'attribute' => 'description',
                    'type' => 'pim_catalog_text',
                    'locale' => 'fr_FR',
                    'scope' => 'print'
                ]
            ]);

        // get existing values
        $skuValue->getAttribute()->willReturn($sku);
        $skuValue->getLocale()->willReturn(null);
        $skuValue->getScope()->willReturn(null);
        $product->getValues()->willReturn([$skuValue]);

        // Create 6 empty product values and add them to the product
        $product->getValue(Argument::cetera())->shouldBeCalledTimes(6)->willReturn(null);
        $product->removeValue(Argument::any())->shouldNotBeCalled();

        $attribute = new $attributeClass();
        $attribute->setCode('attribute');
        $attribute->setBackendType('text');

        $productValueFactory->create(Argument::cetera())
            ->shouldBeCalledTimes(6)
            ->willReturn(new $valueClass($attribute, null, null, null));

        $product->addValue(Argument::any())->shouldBeCalledTimes(6);

        $this->addMissingProductValues($product);
    }

    function it_adds_missing_product_associations(
        $assocTypeRepository,
        ProductInterface $productOne,
        ProductInterface $productTwo,
        AssociationTypeInterface $type
    ) {
        $assocTypeRepository->findMissingAssociationTypes($productOne)->willReturn([$type]);
        $productOne->addAssociation(Argument::any())->shouldBeCalled();
        $this->addMissingAssociations($productOne);

        $assocTypeRepository->findMissingAssociationTypes($productTwo)->willReturn([]);
        $productTwo->addAssociation(Argument::any())->shouldNotBeCalled();
        $this->addMissingAssociations($productTwo);
    }

    function it_adds_an_empty_product_value(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $size,
        AttributeInterface $color,
        ProductValueInterface $sizeValue,
        ProductValueInterface $colorValue
    ) {
        $size->getCode()->willReturn('size');
        $size->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $size->isLocalizable()->willReturn(false);
        $size->isScopable()->willReturn(false);

        $color->getCode()->willReturn('color');
        $color->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $color->isLocalizable()->willReturn(true);
        $color->isScopable()->willReturn(true);

        $product->getValue('size', null, null)->willReturn($sizeValue);
        $product->getValue('color', 'en_US', 'ecommerce')->willReturn($colorValue);

        $product->removeValue($sizeValue)->willReturn($product);
        $product->removeValue($colorValue)->willReturn($product);

        $productValueFactory->create($size, null, null, null)->willReturn($sizeValue);
        $productValueFactory->create($color, 'ecommerce', 'en_US', null)->willReturn($colorValue);

        $product->addValue($sizeValue)->willReturn($product);
        $product->addValue($colorValue)->willReturn($product);

        $this->addOrReplaceProductValue($product, $size, null, null, null);
        $this->addOrReplaceProductValue($product, $color, 'en_US', 'ecommerce', null);
    }

    function it_adds_a_non_empty_product_value(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $size,
        AttributeInterface $color,
        ProductValueInterface $sizeValue,
        ProductValueInterface $colorValue
    ) {
        $size->getCode()->willReturn('size');
        $size->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $size->isLocalizable()->willReturn(false);
        $size->isScopable()->willReturn(false);

        $color->getCode()->willReturn('color');
        $color->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $color->isLocalizable()->willReturn(true);
        $color->isScopable()->willReturn(true);

        $product->getValue('size', null, null)->willReturn($sizeValue);
        $product->getValue('color', 'en_US', 'ecommerce')->willReturn($colorValue);

        $product->removeValue($sizeValue)->willReturn($product);
        $product->removeValue($colorValue)->willReturn($product);

        $productValueFactory->create($size, null, null, null)->willReturn($sizeValue);
        $productValueFactory->create($color, 'ecommerce', 'en_US', 'red')->willReturn($colorValue);

        $product->addValue($sizeValue)->willReturn($product);
        $product->addValue($colorValue)->willReturn($product);

        $this->addOrReplaceProductValue($product, $size, null, null, null);
        $this->addOrReplaceProductValue($product, $color, 'en_US', 'ecommerce', 'red');
    }

    function it_adds_a_product_value_if_there_was_not_a_previous_one(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $label,
        ProductValueInterface $value
    ) {
        $label->getCode()->willReturn('label');
        $label->getType()->willReturn(AttributeTypes::TEXT);
        $label->isLocalizable()->willReturn(false);
        $label->isScopable()->willReturn(false);

        $product->getValue('label', null, null)->willReturn(null);

        $product->removeValue(Argument::any())->shouldNotBeCalled();

        $productValueFactory->create($label, null, null, 'foobar')->willReturn($value);

        $product->addValue($value)->willReturn($product);

        $this->addOrReplaceProductValue($product, $label, null, null, 'foobar');
    }
}
