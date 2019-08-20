<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\CompletenessFamilyMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\CompletenessFamilyMaskPerChannelAndLocale;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetCompletenessFamilyMasks;
use PhpSpec\ObjectBehavior;

class CompletenessCalculatorSpec extends ObjectBehavior
{
    function let(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetCompletenessFamilyMasks $getCompletenessFamilyMasks
    ) {
        $this->beConstructedWith($getCompletenessProductMasks, $getCompletenessFamilyMasks);
    }

    function it_calculates_completeness_for_a_product(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetCompletenessFamilyMasks $getCompletenessFamilyMasks
    ) {
        $productCompleteness = new CompletenessProductMask(5, "michel", "tshirt", [
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

        $familyMask = new CompletenessFamilyMask("tshirt", $familyMasksPerChannelAndLocale);

        $getCompletenessFamilyMasks->fromFamilyCodes(['tshirt'])->willReturn(['tshirt' => $familyMask]);

        $getCompletenessProductMasks->fromProductIdentifiers(['michel'])->willReturn([$productCompleteness]);
        $this->fromProductIdentifier("michel")->shouldBeLike(new ProductCompletenessWithMissingAttributeCodesCollection(5, [
            new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, [1 => 'view']),
            new ProductCompletenessWithMissingAttributeCodes('<all_channels>', '<all_locales>', 1, [])
        ]));
    }

    function it_calculates_completeness_for_multiple_products(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetCompletenessFamilyMasks $getCompletenessFamilyMasks
    ) {
        $michelCompleteness = new CompletenessProductMask(5, "michel", "tshirt", [
            'name-ecommerce-en_US',
            'name-ecommerce-fr_FR',
            'desc-<all_channels>-<all_locales>',
            'price-tablet-fr_FR',
            'size-ecommerce-en_US'
        ]);
        $anotherCompleteness = new CompletenessProductMask(2, "jean", "tshirt", [
            'name-ecommerce-fr_FR',
            'price-tablet-fr_FR',
            'size-ecommerce-en_US'
        ]);

        $familyMasksPerChannelAndLocale = [
            new CompletenessFamilyMaskPerChannelAndLocale('ecommerce', 'en_US', ['name-ecommerce-en_US', 'view-ecommerce-en_US']),
            new CompletenessFamilyMaskPerChannelAndLocale('<all_channels>', '<all_locales>', ['desc-<all_channels>-<all_locales>']),
        ];

        $familyMask = new CompletenessFamilyMask("tshirt", $familyMasksPerChannelAndLocale);

        $getCompletenessFamilyMasks->fromFamilyCodes(['tshirt'])->willReturn(['tshirt' => $familyMask]);

        $getCompletenessProductMasks->fromProductIdentifiers(['michel', 'jean'])->willReturn([$michelCompleteness, $anotherCompleteness]);
        $this->fromProductIdentifiers(["michel", "jean"])->shouldBeLike([
            'michel' => new ProductCompletenessWithMissingAttributeCodesCollection(5, [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, [1 => 'view']),
                new ProductCompletenessWithMissingAttributeCodes('<all_channels>', '<all_locales>', 1, [])
            ]),
            'jean' => new ProductCompletenessWithMissingAttributeCodesCollection(2, [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, ['name', 'view']),
                new ProductCompletenessWithMissingAttributeCodes('<all_channels>', '<all_locales>', 1, ['desc'])
            ]),
        ]);
    }

    function it_returns_an_empty_collection_for_a_product_without_family(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetCompletenessFamilyMasks $getCompletenessFamilyMasks
    ) {
        $productCompleteness = new CompletenessProductMask(5, 'product_without_family', null, []);
        $getCompletenessProductMasks->fromProductIdentifiers(['product_without_family'])->willReturn([$productCompleteness]);

        $getCompletenessFamilyMasks->fromFamilyCodes([])->willReturn([]);


        $this->fromProductIdentifier('product_without_family')->shouldBeLike(
            new ProductCompletenessWithMissingAttributeCodesCollection(5, [])
        );
    }
}
