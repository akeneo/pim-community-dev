<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Indexing;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Indexing\TableValueNormalizer;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use PhpSpec\ObjectBehavior;

class TableValueNormalizerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableValueNormalizer::class);
    }

    function it_only_supports_table_values_in_indexing_format()
    {
        $value = TableValue::value('nutrition', Table::fromNormalized([['foo' => 'bar']]));

        $this->supportsNormalization($value, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldBe(true);
        $this->supportsNormalization(new \stdClass(), ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldBe(false);
        $this->supportsNormalization($value, 'standard')
            ->shouldBe(false);
    }

    function it_normalizes_a_table_value(GetAttributes $getAttributes)
    {
        $value = TableValue::value('nutrition', Table::fromNormalized([['foo' => 'bar']]));

        $getAttributes->forCode('nutrition')->willReturn($this->buildTableAttribute());

        $this->normalize($value)->shouldReturn([
            'nutrition-table' => [
                '<all_channels>' => [
                    '<all_locales>' => [['foo' => 'bar']],
                ],
            ],
        ]);
    }

    function it_normalizes_a_localizable_table_value(GetAttributes $getAttributes)
    {
        $value = TableValue::localizableValue('nutrition', Table::fromNormalized([['foo' => 'bar']]), 'en_US');

        $getAttributes->forCode('nutrition')->willReturn($this->buildTableAttribute(true));

        $this->normalize($value)->shouldReturn([
            'nutrition-table' => [
                '<all_channels>' => [
                    'en_US' => [['foo' => 'bar']],
                ],
            ],
        ]);
    }

    function it_normalizes_a_scopable_table_value(GetAttributes $getAttributes)
    {
        $value = TableValue::scopableValue('nutrition', Table::fromNormalized([['foo' => 'bar']]), 'mobile');

        $getAttributes->forCode('nutrition')->willReturn($this->buildTableAttribute(false, true));

        $this->normalize($value)->shouldReturn([
            'nutrition-table' => [
                'mobile' => [
                    '<all_locales>' => [['foo' => 'bar']],
                ],
            ],
        ]);
    }

    function it_normalizes_a_localizable_scopable_table_value(GetAttributes $getAttributes)
    {
        $value = TableValue::scopableLocalizableValue(
            'nutrition',
            Table::fromNormalized([['foo' => 'bar']]),
            'mobile', 'en_US'
        );

        $getAttributes->forCode('nutrition')->willReturn($this->buildTableAttribute(true, true));

        $this->normalize($value)->shouldReturn([
            'nutrition-table' => [
                'mobile' => [
                    'en_US' => [['foo' => 'bar']],
                ],
            ],
        ]);
    }

    function it_returns_null_when_the_attribute_is_unknown(GetAttributes $getAttributes)
    {
        $value = TableValue::value('nutrition', Table::fromNormalized([['foo' => 'bar']]));
        $getAttributes->forCode('nutrition')->willReturn(null);

        $this->normalize($value)->shouldReturn(null);
    }

    private function buildTableAttribute(bool $isLocalizable = false, bool $isScopable = false): Attribute
    {
        return new Attribute(
            'nutrition',
            AttributeTypes::TABLE,
            [],
            $isLocalizable,
            $isScopable,
            null,
            null,
            null,
            AttributeTypes::BACKEND_TYPE_TABLE,
            []
        );
    }
}
