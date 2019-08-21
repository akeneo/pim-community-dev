<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use PhpSpec\ObjectBehavior;

final class RequiredAttributesMaskSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('family_code', [
                new RequiredAttributesMaskForChannelAndLocale(
                    'ecommerce',
                    'en_US',
                    ['name-ecommerce-en_US', 'view-ecommerce-en_US']
                ),
                new RequiredAttributesMaskForChannelAndLocale(
                    '<all_channels>',
                    '<all_locales>',
                    ['desc-<all_channels>-<all_locales>']
                )
            ]
        );
    }

    public function it_returns_attribute_requirement_mask_for_a_channel_and_a_locale()
    {
        $this->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US')->shouldBeLike(
            new RequiredAttributesMaskForChannelAndLocale(
                'ecommerce',
                'en_US',
                ['name-ecommerce-en_US', 'view-ecommerce-en_US']
            )
        );
    }

    public function it_throws_exception_if_attribute_requirement_mask_not_found()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('requiredAttributesMaskForChannelAndLocale', ['test', 'en_US']);
    }
}
