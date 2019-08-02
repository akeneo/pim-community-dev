<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $standardNormalizer)
    {
        $this->beConstructedWith($standardNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DateTimeNormalizer::class);
    }

    function it_support_dates()
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);

        $this->supportsNormalization($date, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($date, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
    }

    function it_normalizes_product_assocations($standardNormalizer)
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $standardNormalizer->normalize($date, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, ['context'])
            ->willReturn('date');
        $this->normalize($date, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, ['context'])
            ->shouldReturn('date');
    }
}
