<?php

namespace Specification\Akeneo\Channel\Component\Normalizer\Versioning;

use Akeneo\Tool\Bundle\MeasureBundle\Family\LengthFamilyInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Family\WeightFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\TranslationNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Normalizer\Versioning\ChannelNormalizer;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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
        $this->shouldHaveType(ChannelNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_channel_normalization_into_flat(ChannelInterface $channel)
    {
        $this->supportsNormalization($channel, 'flat')->shouldBe(true);
        $this->supportsNormalization($channel, 'csv')->shouldBe(false);
        $this->supportsNormalization($channel, 'json')->shouldBe(false);
        $this->supportsNormalization($channel, 'xml')->shouldBe(false);
    }

    function it_normalizes_channel(
        $translationNormalizer,
        ChannelInterface $channel,
        ChannelNormalizer $channelNormalizerStandard
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

        $this->normalize($channel)->shouldReturn(
            [
                'code'                             => 'my_code',
                'currencies'                       => 'EUR,USD',
                'locales'                          => 'fr_FR,en_US,de_DE,es_ES',
                'label-en_US'                      => 'my_label',
                'label-fr_FR'                      => 'mon_label',
                'conversion_unit-weight_attribute' => 'GRAM',
                'conversion_unit-length_attribute' => 'CENTIMETER',
                'category'                         => 'winter',
            ]
        );
    }
}
