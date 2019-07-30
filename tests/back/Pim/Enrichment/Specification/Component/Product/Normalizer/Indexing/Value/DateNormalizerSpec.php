<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\DateValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\DateNormalizer;

class DateNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DateNormalizer::class);
    }

    function it_support_dates_for_both_indexing_formats(DateValueInterface $dateValue)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization($dateValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($dateValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
    }
}
