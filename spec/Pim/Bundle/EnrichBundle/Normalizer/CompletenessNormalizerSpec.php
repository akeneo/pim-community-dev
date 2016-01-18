<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\CompletenessInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Prophecy\Argument;

class CompletenessNormalizerSpec extends ObjectBehavior
{
    function it_supports_completenesses(CompletenessInterface $completeness)
    {
        $this->supportsNormalization($completeness, 'internal_api')->shouldReturn(true);
    }

    function it_normalize_completness(CompletenessInterface $completeness, LocaleInterface $en, ChannelInterface $mobile)
    {
        $completeness->getRequiredCount()->willReturn(10);
        $completeness->getMissingCount()->willReturn(2);
        $completeness->getRatio()->willReturn(20);
        $completeness->getLocale()->willReturn($en);
        $completeness->getChannel()->willReturn($mobile);
        $en->getCode()->willReturn('en_US');
        $mobile->getCode()->willReturn('mobile');

        $this->normalize($completeness, 'internal_api', [])->shouldReturn([
                'required' => 10,
                'missing'  => 2,
                'ratio'    => 20,
                'locale'   => 'en_US',
                'channel'  => 'mobile'
            ]);
    }
}
