<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Utils;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Structure\Component\AttributeTypes;

abstract class CatalogProvider
{
    public static function aListOfChannelsWithLocales(array $localesByChannel = ['a_channel' => ['en_US', 'fr_FR']]): ChannelLocaleCollection
    {
        return new ChannelLocaleCollection($localesByChannel);
    }

    public static function anAttribute(string $code = 'an_attribute', string $type = AttributeTypes::TEXT, bool $isLocalizable = false): Attribute
    {
        return new Attribute(new AttributeCode($code), new AttributeType($type), $isLocalizable);
    }

    public static function aListOfProductValues(array $params = []): ProductValuesCollection
    {
        $collection = new ProductValuesCollection();

        foreach ($params as $data) {
            ['attribute' => $attribute, 'values' => $rawValues] = $data;
            $collection->add(new ProductValues($attribute, ChannelLocaleDataCollection::fromNormalizedChannelLocaleData($rawValues, fn ($data) => $data)));
        }

        return $collection;
    }
}
