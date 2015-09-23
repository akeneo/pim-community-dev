<?php

namespace spec\Pim\Bundle\CatalogBundle\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Event\ProductEvents;
use Pim\Bundle\CatalogBundle\Manager\AttributeValuesResolver;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductBuilderSpec extends ObjectBehavior
{
    const PRODUCT_CLASS = 'Pim\Bundle\CatalogBundle\Model\Product';
    const VALUE_CLASS   = 'Pim\Bundle\CatalogBundle\Model\ProductValue';
    const PRICE_CLASS   = 'Pim\Bundle\CatalogBundle\Entity\ProductPrice';
    const ASSOCIATION_CLASS = 'Pim\Bundle\CatalogBundle\Model\Association';

    function let(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        CurrencyRepositoryInterface $currencyRepository,
        AssociationTypeRepositoryInterface $assocTypeRepository,
        EventDispatcherInterface $eventDispatcher,
        AttributeValuesResolver $valuesResolver
    ) {
        $entityConfig = array(
            'product' => self::PRODUCT_CLASS,
            'product_value' => self::VALUE_CLASS,
            'product_price' => self::PRICE_CLASS,
            'association' => self::ASSOCIATION_CLASS
        );

        $this->beConstructedWith(
            $attributeRepository,
            $familyRepository,
            $currencyRepository,
            $assocTypeRepository,
            $eventDispatcher,
            $valuesResolver,
            $entityConfig
        );
    }

    function it_creates_product_without_family($attributeRepository, $eventDispatcher, AttributeInterface $skuAttribute)
    {
        $attributeRepository->getIdentifier()->willReturn($skuAttribute);
        $eventDispatcher->dispatch(ProductEvents::CREATE, Argument::any());

        $this->createProduct()->shouldReturnAnInstanceOf(self::PRODUCT_CLASS);
    }

    function it_creates_product_with_a_family($attributeRepository, $familyRepository, $eventDispatcher, AttributeInterface $skuAttribute, FamilyInterface $tshirtFamily)
    {
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

        $this->createProduct("mysku", "tshirt")->shouldReturnAnInstanceOf(self::PRODUCT_CLASS);
    }

    function it_adds_missing_product_values_from_family_on_new_product(
        $valuesResolver,
        FamilyInterface $family,
        ProductInterface $product,
        AttributeInterface $sku,
        AttributeInterface $name,
        AttributeInterface $desc,
        ProductValueInterface $skuValue
    ) {
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
        $valuesResolver->resolveEligibleValues(['sku' => $sku, 'name' => $name, 'description' => $desc])->willReturn(
            [
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
            ]
        );

        // get existing values
        $skuValue->getAttribute()->willReturn($sku);
        $skuValue->getLocale()->willReturn(null);
        $skuValue->getScope()->willReturn(null);
        $product->getValues()->willReturn([$skuValue]);

        // add 6 new values : 4 desc (locales x scopes) + 2 name (locales
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

    function it_adds_product_value(ProductInterface $product, AttributeInterface $size)
    {
        $size->isLocalizable()->willReturn(false);
        $size->isScopable()->willReturn(false);
        $product->addValue(Argument::any())->shouldBeCalled();

        $this->addProductValue($product, $size);
    }

    function it_throws_exception_when_locale_is_not_provided_but_expected(ProductInterface $product, AttributeInterface $name)
    {
        $name->getCode()->willReturn('name');
        $name->isLocalizable()->willReturn(true);
        $name->isScopable()->willReturn(false);

        $this->shouldThrow(
            new \InvalidArgumentException('A locale must be provided to create a value for the localizable attribute name')
        )->duringAddProductValue($product, $name);
    }

    function it_throws_exception_when_scope_is_not_provided_but_expected(ProductInterface $product, AttributeInterface $price)
    {
        $price->getCode()->willReturn('price');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(true);

        $this->shouldThrow(
            new \InvalidArgumentException('A scope must be provided to create a value for the scopable attribute price')
        )->duringAddProductValue($product, $price);
    }
}
