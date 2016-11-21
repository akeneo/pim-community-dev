<?php

namespace spec\Pim\Component\Catalog\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\CategoryTranslationInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Normalizer\Structured\TranslationNormalizer;
use Prophecy\Argument;

class ChannelNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $transNormalizer)
    {
        $this->beConstructedWith($transNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Structured\ChannelNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_channel_normalization_into_json_and_xml(ChannelInterface $channel)
    {
        $this->supportsNormalization($channel, 'csv')->shouldBe(false);
        $this->supportsNormalization($channel, 'json')->shouldBe(true);
        $this->supportsNormalization($channel, 'xml')->shouldBe(true);
    }

    function it_normalizes_channel(
        ChannelInterface $channel,
        CurrencyInterface $eur,
        CurrencyInterface $usd,
        LocaleInterface $en,
        LocaleInterface $fr,
        CategoryInterface $category,
        CategoryTranslationInterface $translation,
        TranslationNormalizer $transNormalizer
    ) {
        $channel->getCode()->willReturn('ecommerce');
        $channel->getCurrencies()->willReturn([$eur, $usd]);
        $eur->getCode()->willReturn('EUR');
        $usd->getCode()->willReturn('USD');
        $channel->getLocales()->willReturn([$en, $fr]);
        $en->getCode()->willReturn('en_US');
        $fr->getCode()->willReturn('fr_FR');
        $channel->getCategory()->willReturn($category);
        $category->getCode()->willReturn('master');
        $category->getId()->willReturn(42);
        $translation->getLabel()->willReturn('label');
        $translation->getLocale()->willReturn('en_US');
        $category->getTranslations()->willReturn([$translation]);
        $channel->getConversionUnits()->willReturn(
            [
                'Weight' => 'Kilogram',
                'Size' => 'Centimeter'
            ]
        );

        $transNormalizer->normalize(Argument::cetera())->willReturn(['labels' => []]);

        $this->normalize($channel)->shouldReturn(
            [
                'code'             => 'ecommerce',
                'currencies'       => ['EUR', 'USD'],
                'locales'          => ['en_US', 'fr_FR'],
                'category'         => [
                    'id' => 42,
                    'code' => 'master',
                    'labels' => [
                        'en_US' => 'label'
                    ],
                ],
                'conversion_units' => 'Weight: Kilogram, Size: Centimeter',
                'labels' => [],
            ]
        );
    }
}
