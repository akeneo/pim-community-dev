<?php

namespace spec\Pim\Component\Catalog\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\PriceFactory;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Manager\AttributeValuesResolver;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\ProductEvents;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductBuilderSpec extends ObjectBehavior
{
    const PRODUCT_CLASS = 'Pim\Component\Catalog\Model\Product';
    const VALUE_CLASS = 'Pim\Component\Catalog\Model\ProductValue';
    const PRICE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\ProductPrice';
    const ASSOCIATION_CLASS = 'Pim\Component\Catalog\Model\Association';

    function let(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        CurrencyRepositoryInterface $currencyRepository,
        AssociationTypeRepositoryInterface $assocTypeRepository,
        EventDispatcherInterface $eventDispatcher,
        AttributeValuesResolver $valuesResolver,
        ProductValueFactory $productValueFactory,
        PriceFactory $priceFactory
    ) {
        $entityConfig = [
            'product' => self::PRODUCT_CLASS,
            'association' => self::ASSOCIATION_CLASS
        ];

        $this->beConstructedWith(
            $attributeRepository,
            $familyRepository,
            $currencyRepository,
            $assocTypeRepository,
            $eventDispatcher,
            $valuesResolver,
            $productValueFactory,
            $priceFactory,
            $entityConfig
        );
    }

    function it_creates_product_without_family(
        $attributeRepository,
        $eventDispatcher,
        $productValueFactory,
        ProductValueInterface $productValue,
        AttributeInterface $skuAttribute
    ) {
        $attributeRepository->getIdentifier()->willReturn($skuAttribute);
        $skuAttribute->getCode()->willReturn('sku');

        $productValue->getAttribute()->willReturn($skuAttribute);
        $productValue->getScope()->willReturn(null);
        $productValue->getLocale()->willReturn(null);
        $productValue->setProduct(Argument::type(self::PRODUCT_CLASS))->shouldBeCalled();
        $productValueFactory->create($skuAttribute, null, null, null)->willReturn($productValue);

        $eventDispatcher->dispatch(ProductEvents::CREATE, Argument::any());

        $this->createProduct()->shouldReturnAnInstanceOf(self::PRODUCT_CLASS);
    }

    function it_creates_product_with_a_family(
        $attributeRepository,
        $familyRepository,
        $eventDispatcher,
        $productValueFactory,
        AttributeInterface $skuAttribute,
        FamilyInterface $tshirtFamily,
        ProductValueInterface $productValue
    ) {
        $attributeRepository->getIdentifier()->willReturn($skuAttribute);

        $skuAttribute->getCode()->willReturn('sku');
        $skuAttribute->getAttributeType()->willReturn('pim_catalog_identifier');
        $skuAttribute->getBackendType()->willReturn('varchar');
        $skuAttribute->isLocalizable()->willReturn(false);
        $skuAttribute->isScopable()->willReturn(false);
        $skuAttribute->isLocaleSpecific()->willReturn(false);
        $skuAttribute->isBackendTypeReferenceData()->willReturn(false);
        $eventDispatcher->dispatch(ProductEvents::CREATE, Argument::any());

        $familyRepository->findOneByIdentifier("tshirt")->willReturn($tshirtFamily);
        $tshirtFamily->getId()->shouldBeCalled();
        $tshirtFamily->getAttributes()->willReturn([]);

        $productValue->getScope()->willReturn(null);
        $productValue->getLocale()->willReturn(null);
        $productValue->setProduct(Argument::type(self::PRODUCT_CLASS))->shouldBeCalled();
        $productValue->getAttribute()->willReturn($skuAttribute);
        $productValueFactory->create($skuAttribute, null, null, 'mysku')->willReturn($productValue);

        $this->createProduct('mysku', 'tshirt')->shouldReturnAnInstanceOf(self::PRODUCT_CLASS);
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
        $valueClass = self::VALUE_CLASS;

        $sku->getCode()->willReturn('sku');
        $sku->getAttributeType()->willReturn('pim_catalog_identifier');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $name->getCode()->willReturn('name');
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $name->isLocalizable()->willReturn(true);
        $name->isScopable()->willReturn(false);

        $desc->getCode()->willReturn('description');
        $desc->getAttributeType()->willReturn('pim_catalog_text');
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
        $productValueFactory->create(Argument::cetera())
            ->shouldBeCalledTimes(6)
            ->willReturn(new $valueClass());
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
        $color->getCode()->willReturn('color');

        $size->isLocalizable()->willReturn(false);
        $size->isScopable()->willReturn(false);

        $color->isLocalizable()->willReturn(true);
        $color->isScopable()->willReturn(true);

        $product->getValue('size', null, null)->shouldBeCalled()->willReturn($sizeValue);
        $product->getValue('color', 'en_US', 'ecommerce')->shouldBeCalled()->willReturn($colorValue);

        $product->removeValue($sizeValue)->shouldBeCalled()->willReturn($product);
        $product->removeValue($colorValue)->shouldBeCalled()->willReturn($product);

        $productValueFactory->create($size, null, null, null)->willReturn($sizeValue);
        $productValueFactory->create($color, 'ecommerce', 'en_US', null)->willReturn($colorValue);

        $product->addValue($sizeValue)->shouldBeCalled()->willReturn($product);
        $product->addValue($colorValue)->shouldBeCalled()->willReturn($product);

        $this->addProductValue($product, $size, null, null, null);
        $this->addProductValue($product, $color, 'en_US', 'ecommerce', null);
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
        $color->getCode()->willReturn('color');

        $size->isLocalizable()->willReturn(false);
        $size->isScopable()->willReturn(false);

        $color->isLocalizable()->willReturn(true);
        $color->isScopable()->willReturn(true);

        $product->getValue('size', null, null)->shouldBeCalled()->willReturn($sizeValue);
        $product->getValue('color', 'en_US', 'ecommerce')->shouldBeCalled()->willReturn($colorValue);

        $product->removeValue($sizeValue)->shouldBeCalled()->willReturn($product);
        $product->removeValue($colorValue)->shouldBeCalled()->willReturn($product);

        $productValueFactory->create($size, null, null, null)->willReturn($sizeValue);
        $productValueFactory->create($color, 'ecommerce', 'en_US', 'red')->willReturn($colorValue);

        $product->addValue($sizeValue)->shouldBeCalled()->willReturn($product);
        $product->addValue($colorValue)->shouldBeCalled()->willReturn($product);

        $this->addProductValue($product, $size, null, null, null);
        $this->addProductValue($product, $color, 'en_US', 'ecommerce', 'red');
    }

    function it_adds_a_product_value_if_there_was_not_a_previous_one(
        $productValueFactory,
        ProductInterface $product,
        AttributeInterface $label,
        ProductValueInterface $value
    ) {
        $label->getCode()->willReturn('label');

        $label->isLocalizable()->willReturn(false);
        $label->isScopable()->willReturn(false);

        $product->getValue('label', null, null)->shouldBeCalled()->willReturn(null);

        $product->removeValue(Argument::any())->shouldNotBeCalled();

        $productValueFactory->create($label, null, null, 'foobar')->willReturn($value);

        $product->addValue($value)->shouldBeCalled()->willReturn($product);

        $this->addProductValue($product, $label, null, null, 'foobar');
    }
}
