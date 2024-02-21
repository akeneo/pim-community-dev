<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeTranslations;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Channel\GetChannelTranslations;
use Akeneo\Tool\Component\Localization\CurrencyTranslator;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;
use Akeneo\Tool\Component\Localization\LanguageTranslator;
use PhpSpec\ObjectBehavior;

class AttributeTranslatorSpec extends ObjectBehavior
{
    function let(
        LabelTranslatorInterface $labelTranslator,
        AttributeColumnsResolver $attributeColumnsResolver,
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        GetChannelTranslations $getChannelTranslationsQuery,
        LanguageTranslator $languageTranslator,
        CurrencyTranslator $currencyTranslator,
        GetAttributeTranslations $getAttributeTranslations
    ) {
        $this->beConstructedWith(
            $labelTranslator,
            $attributeColumnsResolver,
            $attributeColumnInfoExtractor,
            $getChannelTranslationsQuery,
            $languageTranslator,
            $currencyTranslator,
            $getAttributeTranslations
        );
    }

    function it_translates_basic_attributes(
        AttributeColumnsResolver $attributeColumnsResolver,
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        GetChannelTranslations $getChannelTranslationsQuery,
        LanguageTranslator $languageTranslator,
        GetAttributeTranslations $getAttributeTranslations,
        AttributeInterface $name
    ) {
        $getAttributeTranslations->byAttributeCodesAndLocale(['name'], 'fr_FR')->willReturn(['name' => 'Nom', 'description' => 'Description']);
        $attributeColumnsResolver->resolveAttributeColumns()->willReturn([
            'name-en_US-ecommerce',
            'name-fr_FR-ecommerce',
            'name-fr_FR-mobile',
            'name-en_US-mobile',
            'description',
            'price'
        ]);
        $attributeColumnInfoExtractor->extractColumnInfo('name-en_US-ecommerce')->willReturn([
            'attribute'   => $name,
            'locale_code' => 'en_US',
            'scope_code'  => 'ecommerce'
        ]);
        $name->getCode()->willReturn('name');
        $name->isLocalizable()->willReturn(true);
        $name->isScopable()->willReturn(true);
        $name->getType()->willReturn('pim_catalog_text');
        $this->warmup(['name-en_US-ecommerce'], 'fr_FR');

        $languageTranslator->translate('en_US', 'fr_FR', '[en_US]')->willReturn('Anglais Américain');
        $languageTranslator->translate('en_US', 'fr_FR', '[en_US]')->willReturn('Anglais Américain');

        $getChannelTranslationsQuery->byLocale('fr_FR')->willReturn(['ecommerce' => 'E-commerce', 'mobile' => 'Mobile', 'impression' => 'Impression']);

        $this->translate('name-en_US-ecommerce', 'fr_FR')->shouldReturn('Nom (Anglais Américain, E-commerce)');
    }

    function it_translates_metric_attributes(
        LabelTranslatorInterface $labelTranslator,
        AttributeColumnsResolver $attributeColumnsResolver,
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        GetChannelTranslations $getChannelTranslationsQuery,
        LanguageTranslator $languageTranslator,
        GetAttributeTranslations $getAttributeTranslations,
        AttributeInterface $weight
    ) {
        $getAttributeTranslations->byAttributeCodesAndLocale(['weight'], 'fr_FR')->willReturn(['weight' => 'Poids', 'description' => 'Description']);
        $attributeColumnsResolver->resolveAttributeColumns()->willReturn([
            'weight-en_US-ecommerce-unit',
            'weight-fr_FR-ecommerce',
            'weight-fr_FR-mobile',
            'weight-en_US-mobile',
            'description',
            'price'
        ]);
        $attributeColumnInfoExtractor->extractColumnInfo('weight-en_US-ecommerce-unit')->willReturn([
            'attribute'   => $weight,
            'locale_code' => 'en_US',
            'scope_code'  => 'ecommerce'
        ]);
        $weight->getCode()->willReturn('weight');
        $weight->isLocalizable()->willReturn(true);
        $weight->isScopable()->willReturn(true);
        $weight->getType()->willReturn('pim_catalog_metric');
        $this->warmup(['weight-en_US-ecommerce-unit'], 'fr_FR');

        $languageTranslator->translate('en_US', 'fr_FR', '[en_US]')->willReturn('Anglais Américain');
        $labelTranslator->translate('pim_common.unit', 'fr_FR', '[unit]')->willReturn('Unités');

        $getChannelTranslationsQuery->byLocale('fr_FR')->willReturn(['ecommerce' => 'E-commerce', 'mobile' => 'Mobile', 'impression' => 'Impression']);

        $this->translate('weight-en_US-ecommerce-unit', 'fr_FR')->shouldReturn('Poids (Anglais Américain, E-commerce) (Unités)');
    }

    function it_translates_price_attributes(
        AttributeColumnsResolver $attributeColumnsResolver,
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        GetChannelTranslations $getChannelTranslationsQuery,
        LanguageTranslator $languageTranslator,
        CurrencyTranslator $currencyTranslator,
        GetAttributeTranslations $getAttributeTranslations,
        AttributeInterface $price
    ) {
        $getAttributeTranslations->byAttributeCodesAndLocale(['price'], 'fr_FR')->willReturn(['price' => 'Prix', 'description' => 'Description']);
        $attributeColumnsResolver->resolveAttributeColumns()->willReturn([
            'price-en_US-ecommerce-EUR',
            'price-fr_FR-ecommerce',
            'price-fr_FR-mobile',
            'price-en_US-mobile',
            'description',
            'price',
        ]);
        $attributeColumnInfoExtractor->extractColumnInfo('price-en_US-ecommerce-EUR')->willReturn([
            'attribute'      => $price,
            'locale_code'    => 'en_US',
            'scope_code'     => 'ecommerce',
            'price_currency' => 'EUR'
        ]);
        $price->getCode()->willReturn('price');
        $price->isLocalizable()->willReturn(true);
        $price->isScopable()->willReturn(true);
        $price->getType()->willReturn('pim_catalog_price_collection');
        $this->warmup(['price-en_US-ecommerce-EUR'], 'fr_FR');

        $languageTranslator->translate('en_US', 'fr_FR', '[en_US]')->willReturn('Anglais Américain');
        $currencyTranslator->translate('EUR', 'fr_FR', '[EUR]')->willReturn('Euro');

        $getChannelTranslationsQuery->byLocale('fr_FR')->willReturn(['ecommerce' => 'E-commerce', 'mobile' => 'Mobile', 'impression' => 'Impression']);

        $this->translate('price-en_US-ecommerce-EUR', 'fr_FR')->shouldReturn('Prix (Anglais Américain, E-commerce) (Euro)');
    }

    function it_translates_basic_attribute_using_fallbacks(
        AttributeColumnsResolver $attributeColumnsResolver,
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        GetChannelTranslations $getChannelTranslationsQuery,
        LanguageTranslator $languageTranslator,
        CurrencyTranslator $currencyTranslator,
        GetAttributeTranslations $getAttributeTranslations,
        AttributeInterface $price
    ) {
        $getAttributeTranslations->byAttributeCodesAndLocale(['price'], 'fr_FR')->willReturn([]);
        $attributeColumnsResolver->resolveAttributeColumns()->willReturn([
            'price-en_US-ecommerce-EUR',
            'price-fr_FR-ecommerce',
            'price-fr_FR-mobile',
            'price-en_US-mobile',
            'description',
            'price'
        ]);
        $attributeColumnInfoExtractor->extractColumnInfo('price-en_US-ecommerce-EUR')->willReturn([
            'attribute'      => $price,
            'locale_code'    => 'en_US',
            'scope_code'     => 'ecommerce',
            'price_currency' => 'EUR'
        ]);
        $price->getCode()->willReturn('price');
        $price->isLocalizable()->willReturn(true);
        $price->isScopable()->willReturn(true);
        $price->getType()->willReturn('pim_catalog_price_collection');
        $this->warmup(['price-en_US-ecommerce-EUR'], 'fr_FR');

        $languageTranslator->translate('en_US', 'fr_FR', '[en_US]')->willReturn('[en_US]');
        $currencyTranslator->translate('EUR', 'fr_FR', '[EUR]')->willReturn('[EUR]');

        $getChannelTranslationsQuery->byLocale('fr_FR')->willReturn([]);

        $this->translate('price-en_US-ecommerce-EUR', 'fr_FR')->shouldReturn('[price] ([en_US], [ecommerce]) ([EUR])');
    }

    function it_support_attribute_headers($attributeColumnsResolver)
    {
        $attributeColumnsResolver->resolveAttributeColumns()->willReturn([
            'price-en_US-ecommerce-EUR',
            'price-fr_FR-ecommerce',
            'price-fr_FR-mobile',
            'price-en_US-mobile',
            'description',
            'price'
        ]);

        $this->supports('price-en_US-ecommerce-EUR')->shouldReturn(true);
        $this->supports('price-fr_FR-ecommerce')->shouldReturn(true);
        $this->supports('price-fr_FR-mobile')->shouldReturn(true);
        $this->supports('price-en_US-mobile')->shouldReturn(true);
        $this->supports('description')->shouldReturn(true);
        $this->supports('price')->shouldReturn(true);
        $this->supports('yolo')->shouldReturn(false);
    }
}
