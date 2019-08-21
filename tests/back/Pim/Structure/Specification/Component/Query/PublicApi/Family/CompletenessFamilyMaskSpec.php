<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\CompletenessFamilyMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\CompletenessFamilyMaskPerChannelAndLocale;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessWithMissingAttributeCodesCollection;
use PhpSpec\ObjectBehavior;

final class CompletenessFamilyMaskSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('family_code', [
                new CompletenessFamilyMaskPerChannelAndLocale(
                    'ecommerce',
                    'en_US',
                    ['name-ecommerce-en_US', 'view-ecommerce-en_US']
                ),
                new CompletenessFamilyMaskPerChannelAndLocale(
                    '<all_channels>',
                    '<all_locales>',
                    ['desc-<all_channels>-<all_locales>']
                )
            ]
        );
    }

    public function it_returns_attribute_requirement_mask_for_a_channel_and_a_locale()
    {
        $this->familyMaskForChannelAndLocale('ecommerce', 'en_US')->shouldBeLike(
            new CompletenessFamilyMaskPerChannelAndLocale(
                'ecommerce',
                'en_US',
                ['name-ecommerce-en_US', 'view-ecommerce-en_US']
            )
        );
    }

    public function it_throws_exception_if_attribute_requirement_mask_not_found()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('familyMaskForChannelAndLocale', ['test', 'en_US']);
    }
}
