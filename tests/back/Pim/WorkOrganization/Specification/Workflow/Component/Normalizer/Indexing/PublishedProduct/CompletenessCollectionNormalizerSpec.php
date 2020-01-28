<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\PublishedProduct;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\PublishedProduct\CompletenessCollectionNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CompletenessCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_a_published_product_completeness_collection_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldHaveType(CompletenessCollectionNormalizer::class);
    }

    function it_only_normalizes_a_published_product_completeness_collection_for_indexing_formats()
    {
        $collection = new PublishedProductCompletenessCollection(42, []);

        $this->supportsNormalization($collection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
             ->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
             ->shouldReturn(false);
        $this->supportsNormalization($collection, 'other_format')
             ->shouldReturn(false);
    }

    function it_normalizes_a_published_product_completeness_collection()
    {
        $collection = new PublishedProductCompletenessCollection(
            42,
            [
                new PublishedProductCompleteness('ecommerce', 'en_US', 5, []),
                new PublishedProductCompleteness('ecommerce', 'fr_FR', 5, []),
                new PublishedProductCompleteness('print', 'en_US', 5, ['description']),
                new PublishedProductCompleteness('print', 'fr_FR', 5, ['description', 'picture']),
            ]
        );

        $expected = [
            'ecommerce' => [
                'en_US' => 100,
                'fr_FR' => 100,
            ],
            'print' => [
                'en_US' => 80,
                'fr_FR' => 60,
            ],
        ];

        $this->normalize($collection, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
             ->shouldReturn($expected);
    }
}
