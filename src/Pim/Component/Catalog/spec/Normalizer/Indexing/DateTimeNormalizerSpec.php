<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Normalizer\Indexing\DateTimeNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
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
        $this->supportsNormalization(new \stdClass(), ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);

        $this->supportsNormalization($date, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($date, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(true);
        $this->supportsNormalization($date, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
    }

    function it_normalizes_product_assocations($standardNormalizer)
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $standardNormalizer->normalize($date, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX, ['context'])
            ->willReturn('date');
        $this->normalize($date, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX, ['context'])
            ->shouldReturn('date');
    }
}
