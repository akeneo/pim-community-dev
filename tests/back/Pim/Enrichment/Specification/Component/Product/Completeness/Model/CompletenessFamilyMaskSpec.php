<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessFamilyMaskPerChannelAndLocale;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessWithMissingAttributeCodesCollection;
use PhpSpec\ObjectBehavior;

final class CompletenessFamilyMaskSpec extends ObjectBehavior
{
    public function it_returns_the_right_product_completenesss_collection()
    {
        $productCompleteness = new CompletenessProductMask(5, "bob", "tshirt", [
            'name-ecommerce-en_US',
            'name-ecommerce-fr_FR',
            'desc-<all_channels>-<all_locales>',
            'price-tablet-fr_FR',
            'size-ecommerce-en_US'
        ]);

        $familyMasksPerChannelAndLocale = [
            new CompletenessFamilyMaskPerChannelAndLocale('ecommerce', 'en_US', ['name-ecommerce-en_US', 'view-ecommerce-en_US']),
            new CompletenessFamilyMaskPerChannelAndLocale('<all_channels>', '<all_locales>', ['desc-<all_channels>-<all_locales>']),
        ];

        $this->beConstructedWith('tshirt', $familyMasksPerChannelAndLocale);
        $this->completenessCollectionForProduct($productCompleteness)->shouldBeLike(new ProductCompletenessWithMissingAttributeCodesCollection(5, [
            new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, [1 => 'view']),
            new ProductCompletenessWithMissingAttributeCodes('<all_channels>', '<all_locales>', 1, [])
        ]));
    }
}
