<?php

namespace Specification\Akeneo\Asset\Component\Normalizer\Standard;

use Akeneo\Asset\Component\Normalizer\Standard\VariationNormalizer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\ReferenceInterface;
use Akeneo\Asset\Component\Model\VariationInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VariationNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(VariationNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_standard_normalization(VariationInterface $variation)
    {
        $this->supportsNormalization($variation, 'standard')->shouldBe(true);
        $this->supportsNormalization($variation, 'json')->shouldBe(false);
        $this->supportsNormalization($variation, 'xml')->shouldBe(false);
    }

    function it_normalizes_asset_variation(
        VariationInterface $variation,
        AssetInterface $asset,
        LocaleInterface $locale,
        ChannelInterface $channel,
        ReferenceInterface $reference,
        FileInfoInterface $referenceFile,
        FileInfoInterface $variationFile
    ) {
        $variation->getAsset()->willReturn($asset);
        $asset->getCode()->willReturn('paint');
        $variation->getLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');
        $variation->getChannel()->willReturn($channel);
        $channel->getCode()->willReturn('ecommerce');
        $variation->getReference()->willReturn($reference);
        $reference->getFileInfo()->willReturn($referenceFile);
        $referenceFile->getKey()->willReturn('e/f/9/0/d15fe8_photo.jpg');
        $variation->getFileInfo()->willReturn($variationFile);
        $variationFile->getKey()->willReturn('b/9/f/f/f4210_photo_mobile.jpg');

        $this->normalize($variation)->shouldReturn( [
            'asset'          => 'paint',
            'code'           => 'b/9/f/f/f4210_photo_mobile.jpg',
            'locale'         => 'en_US',
            'channel'        => 'ecommerce',
            'reference_file' => 'e/f/9/0/d15fe8_photo.jpg',
        ]);
    }
}
