<?php

namespace Specification\Akeneo\Channel\Component\Normalizer\Standard;

use Akeneo\Channel\Component\Normalizer\Standard\ChannelNormalizer;
use Akeneo\Tool\Bundle\MeasureBundle\Family\LengthFamilyInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Family\WeightFamilyInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\CurrencyInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ChannelNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $translationNormalizer)
    {
        $this->beConstructedWith($translationNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChannelNormalizer::class);
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
        $translationNormalizer,
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
        $channel->getCurrencies()->willReturn([$currencyEUR, $currencyUSD]);
        $channel->getLocaleCodes()->willReturn(['fr_FR', 'en_US', 'de_DE', 'es_ES']);
        $channel->getCategory()->willReturn($category);
        $channel->getConversionUnits()->willReturn($units);

        $category->getCode()->willReturn('winter');

        $currencyEUR->getCode()->willReturn('EUR');
        $currencyUSD->getCode()->willReturn('USD');

        $translationNormalizer->normalize($channel, Argument::any(), [])->willReturn(
            [
                'en_US' => 'My label',
                'fr_FR' => 'Mon label',
            ]
        );

        $this->normalize($channel, 'standard', [])->shouldReturn([
            'code'             => 'my_code',
            'currencies'       => ['EUR', 'USD'],
            'locales'          => ['fr_FR', 'en_US', 'de_DE', 'es_ES'],
            'category_tree'    => 'winter',
            'conversion_units' => $units,
            'labels'           => [
                'en_US' => 'My label',
                'fr_FR' => 'Mon label'
            ]
        ]);
    }
}
