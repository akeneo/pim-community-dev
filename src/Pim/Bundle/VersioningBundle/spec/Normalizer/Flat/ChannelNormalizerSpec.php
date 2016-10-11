<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Akeneo\Bundle\MeasureBundle\Family\LengthFamilyInterface;
use Akeneo\Bundle\MeasureBundle\Family\WeightFamilyInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\TranslationNormalizer;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Normalizer\Standard\ChannelNormalizer;
use Prophecy\Argument;

class ChannelNormalizerSpec extends ObjectBehavior
{
    function let(
        ChannelNormalizer $channelNormalizerStandard,
        TranslationNormalizer $translationNormalizer
    ) {
        $this->beConstructedWith($channelNormalizerStandard, $translationNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\Normalizer\Flat\ChannelNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_channel_normalization_into_flat(ChannelInterface $channel)
    {
        $this->supportsNormalization($channel, 'flat')->shouldBe(true);
        $this->supportsNormalization($channel, 'csv')->shouldBe(false);
        $this->supportsNormalization($channel, 'json')->shouldBe(false);
        $this->supportsNormalization($channel, 'xml')->shouldBe(false);
    }

    function it_normalizes_channel(
        ChannelInterface $channel,
        ChannelNormalizer $channelNormalizerStandard,
        TranslationNormalizer $translationNormalizer
    ) {
        $translationNormalizer->supportsNormalization(Argument::cetera(), 'flat')->willReturn(true);
        $translationNormalizer->normalize(Argument::cetera(), 'flat', [])->willReturn(
            [
                'label-en_US' => 'my_label',
                'label-fr_FR' => 'mon_label',
            ]
        );

        $channelNormalizerStandard->supportsNormalization($channel, 'standard')->willReturn(true);
        $channelNormalizerStandard->normalize($channel, 'standard', [])->willReturn(
            [
                'code'             => 'my_code',
                'labels'           => [
                    'en_US' => 'my_label',
                    'fr_FR' => 'mon_label',
                ],
                'currencies'       => ['EUR', 'USD'],
                'locales'          => ['fr_FR', 'en_US', 'de_DE', 'es_ES'],
                'category_tree'    => 'winter',
                'conversion_units' => [
                    'weight_attribute' => WeightFamilyInterface::GRAM,
                    'length_attribute' => LengthFamilyInterface::CENTIMETER,
                ],
            ]
        );

        $transNormalizer->normalize(Argument::cetera())->willReturn(['labels' => []]);

        $this->normalize($channel)->shouldReturn(
            [
                'code'                             => 'my_code',
                'currencies'                       => 'EUR,USD',
                'locales'                          => 'fr_FR,en_US,de_DE,es_ES',
                'category_tree'                    => 'winter',
                'label-en_US'                      => 'my_label',
                'label-fr_FR'                      => 'mon_label',
                'conversion_unit-weight_attribute' => 'GRAM',
                'conversion_unit-length_attribute' => 'CENTIMETER',
            ]
        );
    }
}
