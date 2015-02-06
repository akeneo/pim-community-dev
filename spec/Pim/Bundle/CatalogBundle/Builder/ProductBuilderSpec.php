<?php

namespace spec\Pim\Bundle\CatalogBundle\Builder;

use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;

class ProductBuilderSpec extends ObjectBehavior
{
    const PRODUCT_CLASS = 'Pim\Bundle\CatalogBundle\Model\Product';
    const VALUE_CLASS   = 'Pim\Bundle\CatalogBundle\Model\ProductValue';
    const PRICE_CLASS   = 'Pim\Bundle\CatalogBundle\Entity\ProductPrice';

    function let(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        CurrencyRepositoryInterface $currencyRepository
    ) {
        $entityConfig = array(
            'product' => self::PRODUCT_CLASS,
            'product_value' => self::VALUE_CLASS,
            'product_price' => self::PRICE_CLASS
        );

        $this->beConstructedWith(
            $channelRepository,
            $localeRepository,
            $currencyRepository,
            $entityConfig
        );
    }

    function it_adds_missing_product_values_from_family_on_new_product(
        FamilyInterface $family,
        ProductInterface $product,
        AttributeInterface $sku,
        AttributeInterface $name,
        AttributeInterface $desc,
        $localeRepository,
        LocaleInterface $fr,
        LocaleInterface $en,
        $channelRepository,
        ChannelInterface $ecom,
        ChannelInterface $print,
        ProductValueInterface $skuValue
    ) {
        // get expected attributes
        $product->getAttributes()->willReturn([$sku]);
        $product->getFamily()->willReturn($family);
        $family->getAttributes()->willReturn([$sku, $name, $desc]);

        // get expected values
        $sku->getCode()->willReturn('sku');
        $sku->getAttributeType()->willReturn('pim_catalog_identifier');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);
        $sku->isLocaleSpecific()->willReturn(false);

        $name->getCode()->willReturn('name');
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $name->isLocalizable()->willReturn(true);
        $name->isScopable()->willReturn(false);
        $name->isLocaleSpecific()->willReturn(false);

        $desc->getCode()->willReturn('desc');
        $desc->getAttributeType()->willReturn('pim_catalog_text');
        $desc->isLocalizable()->willReturn(true);
        $desc->isScopable()->willReturn(true);
        $desc->isLocaleSpecific()->willReturn(false);

        $fr->getCode()->willReturn('fr_FR');
        $en->getCode()->willReturn('fr_FR');
        $localeRepository->getActivatedLocales()->willReturn([$fr, $en]);

        $ecom->getCode()->willReturn('ecom');
        $ecom->getLocales()->willReturn([$en, $fr]);
        $print->getCode()->willReturn('print');
        $print->getLocales()->willReturn([$en, $fr]);
        $channelRepository->findAll()->willReturn([$ecom, $print]);

        // get existing values
        $skuValue->getAttribute()->willReturn($sku);
        $skuValue->getLocale()->willReturn(null);
        $skuValue->getScope()->willReturn(null);
        $product->getValues()->willReturn([$skuValue]);

        // add 6 new values : 4 desc (locales x scopes) + 2 name (locales
        $product->addValue(Argument::any())->shouldBeCalledTimes(6);

        $this->addMissingProductValues($product);
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
