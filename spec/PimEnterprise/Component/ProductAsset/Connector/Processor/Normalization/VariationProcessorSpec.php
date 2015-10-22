<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\Processor\Normalization;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class VariationProcessorSpec extends ObjectBehavior
{
    function let(
        SerializerInterface $serializer,
        LocaleRepositoryInterface $localeRepository,
        NormalizerInterface $assetNormalizer
    ) {
        $this->beConstructedWith($serializer, $localeRepository, $assetNormalizer);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldImplement('Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\Processor');
    }

    function it_processes($assetNormalizer, $serializer, $localeRepository, AssetInterface $asset)
    {
        $values = [
            'asset'          => 'paint',
            'locale'         => 'en_US',
            'channel'        => 'ecommerce',
            'reference_file' => 'e/f/9/0/d15fe8_photo.jpg',
            'variation_file' => 'b/9/f/f/f4210_photo_mobile.jpg',
        ];

        $result = [
            'asset,locale,channel,reference_file,variation_file',
            'paint;en_US;ecommerce;"e/f/9/0/d15fe8_photo.jpg";"b/9/f/f/f4210_photo_mobile.jpg"'
        ];
        $assetNormalizer->normalize($asset)->willReturn($values);
        $serializer->serialize($values, 'csv',
            [
                'delimiter'     => ';',
                'enclosure'     => '"',
                'withHeader'    => true,
                'heterogeneous' => false,
                'locales'       => 'en_US',
            ])->willReturn($result);

        $localeRepository->getActivatedLocaleCodes()->willReturn('en_US');

        $this
            ->process($asset)
            ->shouldReturn($result);
    }
}
