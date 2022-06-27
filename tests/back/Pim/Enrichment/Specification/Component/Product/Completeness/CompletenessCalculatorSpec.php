<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetRequiredAttributesMasks;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class CompletenessCalculatorSpec extends ObjectBehavior
{
    function let(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetRequiredAttributesMasks $getRequiredAttributesMasks
    ) {
        $this->beConstructedWith($getCompletenessProductMasks, $getRequiredAttributesMasks);
    }

    function it_calculates_completeness_for_a_product(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetRequiredAttributesMasks $getRequiredAttributesMasks
    ) {
        $uuid = Uuid::fromString('3bf35583-c54e-4f8a-8bd9-5693c142a1cf');
        $productCompleteness = new CompletenessProductMask($uuid, "michel", "tshirt", [
            'name-ecommerce-en_US',
            'name-ecommerce-fr_FR',
            'desc-<all_channels>-<all_locales>',
            'price-tablet-fr_FR',
            'size-ecommerce-en_US'
        ]);

        $requiredAttributesMasksPerChannelAndLocale = [
            new RequiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US', ['name-ecommerce-en_US', 'view-ecommerce-en_US']),
            new RequiredAttributesMaskForChannelAndLocale('<all_channels>', '<all_locales>', ['desc-<all_channels>-<all_locales>']),
        ];

        $requiredAttributesMask = new RequiredAttributesMask("tshirt", $requiredAttributesMasksPerChannelAndLocale);

        $getRequiredAttributesMasks->fromFamilyCodes(['tshirt'])->willReturn(['tshirt' => $requiredAttributesMask]);

        $getCompletenessProductMasks->fromProductIdentifiers(['michel'])->willReturn([$productCompleteness]);
        $this->fromProductIdentifier("michel")->shouldBeLike(new ProductCompletenessWithMissingAttributeCodesCollection($uuid, [
            new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, [1 => 'view']),
            new ProductCompletenessWithMissingAttributeCodes('<all_channels>', '<all_locales>', 1, [])
        ]));
    }

    function it_calculates_completeness_for_multiple_products(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetRequiredAttributesMasks $getRequiredAttributesMasks
    ) {
        $michelUuid = Uuid::fromString('3bf35583-c54e-4f8a-8bd9-5693c142a1cf');
        $michelCompleteness = new CompletenessProductMask($michelUuid, "michel", "tshirt", [
            'name-ecommerce-en_US',
            'name-ecommerce-fr_FR',
            'desc-<all_channels>-<all_locales>',
            'price-tablet-fr_FR',
            'size-ecommerce-en_US'
        ]);
        $anotherUuid = Uuid::fromString('fbbee246-ba5b-4dd2-810c-f5669f887e64');
        $anotherCompleteness = new CompletenessProductMask($anotherUuid, "jean", "tshirt", [
            'name-ecommerce-fr_FR',
            'price-tablet-fr_FR',
            'size-ecommerce-en_US'
        ]);

        $requiredAttributesMasksPerChannelAndLocale = [
            new RequiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US', ['name-ecommerce-en_US', 'view-ecommerce-en_US']),
            new RequiredAttributesMaskForChannelAndLocale('<all_channels>', '<all_locales>', ['desc-<all_channels>-<all_locales>']),
        ];

        $requiredAttributesMask = new RequiredAttributesMask("tshirt", $requiredAttributesMasksPerChannelAndLocale);

        $getRequiredAttributesMasks->fromFamilyCodes(['tshirt'])->willReturn(['tshirt' => $requiredAttributesMask]);

        $getCompletenessProductMasks->fromProductIdentifiers(['michel', 'jean'])->willReturn([$michelCompleteness, $anotherCompleteness]);
        $this->fromProductIdentifiers(["michel", "jean"])->shouldBeLike([
            'michel' => new ProductCompletenessWithMissingAttributeCodesCollection($michelUuid, [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, [1 => 'view']),
                new ProductCompletenessWithMissingAttributeCodes('<all_channels>', '<all_locales>', 1, [])
            ]),
            'jean' => new ProductCompletenessWithMissingAttributeCodesCollection($anotherUuid, [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, ['name', 'view']),
                new ProductCompletenessWithMissingAttributeCodes('<all_channels>', '<all_locales>', 1, ['desc'])
            ]),
        ]);
    }

    function it_returns_an_empty_collection_for_a_product_without_family(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetRequiredAttributesMasks $getRequiredAttributesMasks
    ) {
        $uuid = Uuid::fromString('3bf35583-c54e-4f8a-8bd9-5693c142a1cf');
        $productCompleteness = new CompletenessProductMask($uuid, 'product_without_family', null, []);
        $getCompletenessProductMasks->fromProductIdentifiers(['product_without_family'])->willReturn([$productCompleteness]);

        $getRequiredAttributesMasks->fromFamilyCodes([])->willReturn([]);


        $this->fromProductIdentifier('product_without_family')->shouldBeLike(
            new ProductCompletenessWithMissingAttributeCodesCollection($uuid, [])
        );
    }
}
