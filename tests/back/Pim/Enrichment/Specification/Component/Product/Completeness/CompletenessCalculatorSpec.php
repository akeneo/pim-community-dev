<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
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
        $productCompleteness = new CompletenessProductMask($uuid, "tshirt", [
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

        $getCompletenessProductMasks->fromProductUuids([$uuid])->willReturn([$productCompleteness]);
        $this->fromProductUuid($uuid)->shouldBeLike(new ProductCompletenessWithMissingAttributeCodesCollection($uuid, [
            new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, [1 => 'view']),
            new ProductCompletenessWithMissingAttributeCodes('<all_channels>', '<all_locales>', 1, [])
        ]));
    }

    function it_calculates_completeness_for_multiple_products(
        GetCompletenessProductMasks $getCompletenessProductMasks,
        GetRequiredAttributesMasks $getRequiredAttributesMasks
    ) {
        $michelUuid = Uuid::fromString('3bf35583-c54e-4f8a-8bd9-5693c142a1cf');
        $michelCompleteness = new CompletenessProductMask($michelUuid, "tshirt", [
            'name-ecommerce-en_US',
            'name-ecommerce-fr_FR',
            'desc-<all_channels>-<all_locales>',
            'price-tablet-fr_FR',
            'size-ecommerce-en_US'
        ]);
        $jeanUuid = Uuid::fromString('fbbee246-ba5b-4dd2-810c-f5669f887e64');
        $anotherCompleteness = new CompletenessProductMask($jeanUuid, "tshirt", [
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

        $getCompletenessProductMasks->fromProductUuids([$michelUuid, $jeanUuid])->willReturn([$michelCompleteness, $anotherCompleteness]);
        $this->fromProductUuids([$michelUuid, $jeanUuid])->shouldBeLike([
            $michelUuid->toString() => new ProductCompletenessWithMissingAttributeCodesCollection($michelUuid, [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 2, [1 => 'view']),
                new ProductCompletenessWithMissingAttributeCodes('<all_channels>', '<all_locales>', 1, [])
            ]),
            $jeanUuid->toString() => new ProductCompletenessWithMissingAttributeCodesCollection($jeanUuid, [
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
        $productCompleteness = new CompletenessProductMask($uuid, null, []);
        $getCompletenessProductMasks->fromProductUuids([$uuid])->willReturn([$productCompleteness]);

        $getRequiredAttributesMasks->fromFamilyCodes([])->willReturn([]);


        $this->fromProductUuid($uuid)->shouldBeLike(
            new ProductCompletenessWithMissingAttributeCodesCollection($uuid, [])
        );
    }
}
