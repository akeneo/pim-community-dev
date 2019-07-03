<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductCompletenessCollectionNormalizer;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Prophecy\Argument;

class ProductCompletenessCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductCompletenessCollectionNormalizer::class);
    }

    function it_supports_only_indexing_formats_for_completenesses(\stdClass $toNormalize)
    {
        $this->supportsNormalization(Argument::any(), 'foo')->shouldReturn(false);
        $this->supportsNormalization($toNormalize, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($toNormalize, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
    }

    function it_supports_completenesses_for_indexing_formats(
        Collection $completenesses
    ) {
        $completenesses->isEmpty()->willReturn(false);
        $completeness = new ProductCompleteness('channelCode', 'localeCode', 0, []);
        $completenesses->first()->willReturn($completeness);

        $this->supportsNormalization($completenesses, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(true);
    }

    function it_normalizes_completenesses(
        Collection $completenesses,
        \ArrayIterator $completenessesIterator
    ) {
        $completeness1 = new ProductCompleteness('ecommerce', 'en_US', 0, []);
        $completeness2 = new ProductCompleteness('ecommerce', 'fr_FR', 1, []);
        $completeness3 = new ProductCompleteness('ecommerce', 'de_DE', 2, ['fake_attr']);
        $completeness4 = new ProductCompleteness('tablet', 'en_US', 3, ['fake_attr']);

        $completenesses->getIterator()->willReturn($completenessesIterator);
        $completenessesIterator->rewind()->shouldBeCalled();
        $completenessesIterator->valid()->willReturn(true, true, true, true, false);
        $completenessesIterator->current()->willReturn($completeness1, $completeness2, $completeness3, $completeness4);
        $completenessesIterator->next()->shouldBeCalled();

        $this->normalize($completenesses, 'indexing')->shouldReturn(
            [
                'ecommerce' => [
                    'en_US' => 100,
                    'fr_FR' => 100,
                    'de_DE' => 50,
                ],
                'tablet' => [
                    'en_US' => 66
                ]
            ]
        );
    }
}
