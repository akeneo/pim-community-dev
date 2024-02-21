<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\FlatAttributeValueTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValueRegistry;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeValuesFlatTranslatorSpec extends ObjectBehavior
{
    function let(
        AttributeValueRegistry $attributeValueRegistry,
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        AttributeColumnsResolver $attributeColumnsResolver
    ) {
        $this->beConstructedWith(
            $attributeValueRegistry,
            $attributeColumnInfoExtractor,
            $attributeColumnsResolver,
        );
    }

    function it_supports_attribute_columns(AttributeColumnsResolver $attributeColumnsResolver)
    {
        $attributeColumnsResolver->resolveAttributeColumns()->willReturn([
            'name-en_US-ecommerce',
            'name-fr_FR-ecommerce',
            'name-fr_FR-mobile',
            'name-en_US-mobile',
            'description',
            'price'
        ]);

        $this->supports('name-en_US-ecommerce')->shouldReturn(true);
        $this->supports('name-fr_FR-ecommerce')->shouldReturn(true);
        $this->supports('name-fr_FR-mobile')->shouldReturn(true);
        $this->supports('description')->shouldReturn(true);
        $this->supports('X_SELL-products')->shouldReturn(false);
        $this->supports('SOMETHING_ELSE')->shouldReturn(false);
    }

    function it_translates_basic_attribute_values(
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        AttributeColumnsResolver $attributeColumnsResolver,
        AttributeInterface $name,
        AttributeValueRegistry $attributeValueRegistry,
        FlatAttributeValueTranslatorInterface $translator
    ) {
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
        $name->getType()->willReturn('pim_catalog_text');
        $name->getProperties()->willReturn(['my_property' => 'not null value']);
        $name->getMetricFamily()->willReturn(null);

        $attributeValueRegistry->getTranslator('pim_catalog_text', 'name-en_US-ecommerce')
            ->willReturn($translator);
        $translator->translate('name', ['my_property' => 'not null value'], ['value1', 'value2'], 'fr_FR')
            ->willReturn(['valeur une', 'valeur deux']);

        $this->translate('name-en_US-ecommerce', ['value1', 'value2'], 'fr_FR')->shouldReturn(['valeur une', 'valeur deux']);
    }
}
