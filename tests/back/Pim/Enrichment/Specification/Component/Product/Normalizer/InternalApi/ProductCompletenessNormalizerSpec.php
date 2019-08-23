<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;

class ProductCompletenessNormalizerSpec extends ObjectBehavior
{
    function it_supports_completenesses()
    {
        $completeness = new ProductCompletenessWithMissingAttributeCodes('channelCode', 'localeCode', 0, []);
        $this->supportsNormalization($completeness, 'internal_api')->shouldReturn(true);
    }

    function it_normalizes_completeness(
        LocaleInterface $locale,
        ChannelInterface $mobile
    ) {
        $completeness = new ProductCompletenessWithMissingAttributeCodes('mobile', 'en_US', 10, ['fake_attr']);

        $this->normalize($completeness, 'internal_api', [])->shouldReturn([
            'required' => 10,
            'missing'  => 1,
            'ratio'    => 90,
            'locale'   => 'en_US',
            'channel'  => 'mobile',
        ]);
    }
}
