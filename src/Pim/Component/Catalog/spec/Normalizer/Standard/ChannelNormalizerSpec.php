<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard;

use Akeneo\Bundle\MeasureBundle\Family\LengthFamilyInterface;
use Akeneo\Bundle\MeasureBundle\Family\WeightFamilyInterface;
use Akeneo\Component\Classification\Model\CategoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;

class ChannelNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\ChannelNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(ChannelInterface $channel)
    {
        $this->supportsNormalization($channel, 'standard')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($channel, 'xml')->shouldReturn(false);
        $this->supportsNormalization($channel, 'json')->shouldReturn(false);
    }

    function it_normalizes_channel(
        ChannelInterface $channel,
        CategoryInterface $category,
        CurrencyInterface $currencyUSD,
        CurrencyInterface $currencyEUR
    ) {
        $units = [
            'weight_attribute' => WeightFamilyInterface::GRAM,
            'length_attribute' => LengthFamilyInterface::CENTIMETER,
        ];

        $channel->getCode()->willReturn('my_code');
        $channel->getLabel()->willReturn('my_label');
        $channel->getCurrencies()->willReturn([$currencyEUR, $currencyUSD]);
        $channel->getLocaleCodes()->willReturn(['fr_FR', 'en_US', 'de_DE', 'es_ES']);
        $channel->getCategory()->willReturn($category);
        $channel->getConversionUnits()->willReturn($units);

        $category->getCode()->willReturn('winter');

        $currencyEUR->getCode()->willReturn('EUR');
        $currencyUSD->getCode()->willReturn('USD');

        $this->normalize($channel, 'standard', [])->shouldReturn([
            'code'             => 'my_code',
            'label'            => 'my_label',
            'currencies'       => ['EUR', 'USD'],
            'locales'          => ['fr_FR', 'en_US', 'de_DE', 'es_ES'],
            'category_tree'    => 'winter',
            'conversion_units' => $units,
        ]);
    }
}
