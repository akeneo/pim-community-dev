<?php

namespace spec\PimEnterprise\Component\ProductAsset\Normalizer\Flat;

use Akeneo\Component\FileStorage\Model\FileInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;

class AssetVariationNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_should_normalize(
        VariationInterface $variation,
        AssetInterface $asset,
        LocaleInterface $locale,
        ChannelInterface $channel,
        ReferenceInterface $reference,
        FileInterface $referenceFile,
        FileInterface $variationFile
    ) {
        $normalizedValues = [
            'asset'          => 'paint',
            'locale'         => 'en_US',
            'channel'        => 'ecommerce',
            'reference_file' => 'e/f/9/0/d15fe8_photo.jpg',
            'variation_file' => 'b/9/f/f/f4210_photo_mobile.jpg',
        ];

        $variation->getAsset()->willReturn($asset);
        $asset->getCode()->willReturn('paint');
        $variation->getLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');
        $variation->getChannel()->willReturn($channel);
        $channel->getCode()->willReturn('ecommerce');
        $variation->getReference()->willReturn($reference);
        $reference->getFile()->willReturn($referenceFile);
        $referenceFile->getKey()->willReturn('e/f/9/0/d15fe8_photo.jpg');
        $variation->getFile()->willReturn($variationFile);
        $variationFile->getKey()->willReturn('b/9/f/f/f4210_photo_mobile.jpg');

        $this->normalize($variation)->shouldReturn($normalizedValues);
    }
}
