<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
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
        $attributeRequirementMask = new RequiredAttributesMask('family_code', [
            new RequiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US', ['name-ecommerce-en_US', 'view-ecommerce-en_US', 'desc-<all_channels>-<all_locales>']),
            new RequiredAttributesMaskForChannelAndLocale('tablet', 'fr_FR', ['desc-<all_channels>-<all_locales>']),
        ]);

        $this->completenessCollectionForProduct($attributeRequirementMask)->shouldBeLike(
            new ProductCompletenessWithMissingAttributeCodesCollection(1, [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 3, [1 => 'view']),
                new ProductCompletenessWithMissingAttributeCodes('tablet', 'fr_FR', 1, [])
            ])
        );
    }

    function it_returns_an_empty_product_completeness_collection_when_there_is_no_attribute_requirement_mask_because_the_product_is_not_in_a_family()
    {
        $this->beConstructedWith(1, 'identifier', null, []);

        $this->completenessCollectionForProduct(null)->shouldBeLike(
            new ProductCompletenessWithMissingAttributeCodesCollection(1, [])
        );
    }


    function it_throws_an_exception_when_there_is_no_attribute_requirement_mask_but_the_product_is_in_a_family()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('completenessCollectionForProduct', [null]);
    }

    function it_throws_an_exception_when_there_is_an_attribute_requirement_mask_but_the_product_is_not_in_a_family()
    {
        $this->beConstructedWith(1, 'identifier', null, []);

        $attributeRequirementMask = new RequiredAttributesMask('family_code', [
            new RequiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US', ['name-ecommerce-en_US', 'view-ecommerce-en_US', 'desc-<all_channels>-<all_locales>']),
            new RequiredAttributesMaskForChannelAndLocale('tablet', 'fr_FR', ['desc-<all_channels>-<all_locales>']),
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->during('completenessCollectionForProduct', [$attributeRequirementMask]);
    }
}
