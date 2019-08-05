<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing;

use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductCompletenessCollectionNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductCompletenessCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_a_product_completeness_collection_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldHaveType(ProductCompletenessCollectionNormalizer::class);
    }

    function it_only_supports_indexing_formats_for_completenesses()
    {
        $this->supportsNormalization(Argument::any(), 'foo')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
             ->shouldReturn(false);
    }

    function it_supports_completenesses_for_indexing_formats()
    {
        $completenesses = new ProductCompletenessCollection(42, []);
        $this->supportsNormalization($completenesses, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
             ->shouldReturn(true);
    }

    function it_normalizes_completenesses()
    {
        $completenesses = new ProductCompletenessCollection(
            42,
            [
                new ProductCompleteness('ecommerce', 'en_US', 0, []),
                new ProductCompleteness('ecommerce', 'fr_FR', 1, []),
                new ProductCompleteness('ecommerce', 'de_DE', 2, ['fake_attr']),
                new ProductCompleteness('tablet', 'en_US', 3, ['fake_attr']),
            ]
        );

        $this->normalize($completenesses, 'indexing')->shouldReturn(
            [
                'ecommerce' => [
                    'en_US' => 100,
                    'fr_FR' => 100,
                    'de_DE' => 50,
                ],
                'tablet' => [
                    'en_US' => 66,
                ],
            ]
        );
    }
}
