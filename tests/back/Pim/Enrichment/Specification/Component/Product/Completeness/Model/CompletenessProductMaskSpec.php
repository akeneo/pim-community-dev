<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\CompletenessFamilyMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\CompletenessFamilyMaskPerChannelAndLocale;
use PhpSpec\ObjectBehavior;

class CompletenessProductMaskSpec extends ObjectBehavior
{
    function let() {
        $this->beConstructedWith(1, 'identifier', 'family_code', [
            'name-ecommerce-en_US',
            'name-ecommerce-fr_FR',
            'desc-<all_channels>-<all_locales>',
            'price-tablet-fr_FR',
            'size-ecommerce-en_US'
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(CompletenessProductMask::class);
    }

    function it_returns_the_product_completeness_collection_from_an_attribute_requirement_mask()
    {
        $attributeRequirementMask = new CompletenessFamilyMask('family_code', [
            new CompletenessFamilyMaskPerChannelAndLocale('ecommerce', 'en_US', ['name-ecommerce-en_US', 'view-ecommerce-en_US', 'desc-<all_channels>-<all_locales>']),
            new CompletenessFamilyMaskPerChannelAndLocale('tablet', 'fr_FR', ['desc-<all_channels>-<all_locales>']),
        ]);

        $this->completenessCollectionForProduct($attributeRequirementMask)->shouldBeLike(
            new ProductCompletenessWithMissingAttributeCodesCollection(1, [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 3, [1 => 'view']),
                new ProductCompletenessWithMissingAttributeCodes('tablet', 'fr_FR', 1, [])
            ])
        );
    }
}
