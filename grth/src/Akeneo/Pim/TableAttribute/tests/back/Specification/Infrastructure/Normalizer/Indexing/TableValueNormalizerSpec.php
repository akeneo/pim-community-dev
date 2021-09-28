<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Indexing;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Indexing\TableValueNormalizer;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TableValueNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TableValueNormalizer::class);
        $this->shouldImplement(NormalizerInterface::class);
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

    function it_normalizes_a_table_value_to_empty_array()
    {
        $value = TableValue::value('nutrition', Table::fromNormalized([['foo' => 'bar']]));

        $this->normalize($value)->shouldReturn([]);
    }
}
