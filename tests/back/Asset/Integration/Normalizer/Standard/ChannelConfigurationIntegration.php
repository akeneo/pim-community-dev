<?php

namespace AkeneoTestEnterprise\Asset\Integration\Normalizer\Standard;

use AkeneoTestEnterprise\Asset\Integration\Normalizer\Standard\AbstractStandardNormalizerTestCase;

class ChannelConfigurationIntegration extends AbstractStandardNormalizerTestCase
{
    public function testChannelConfiguration()
    {
        $expected = [
            'channel'       => 'tablet',
            'configuration' => [
                'resize' => [
                    'width'  => 250,
                    'height' => 200
                ],
                'colorspace' => [
                    'colorspace' => 'gray'
                ]
            ]
        ];

        $repository = $this->get('pimee_product_asset.repository.channel_configuration');
        $serializer = $this->get('pim_standard_format_serializer');

        $tablet = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('tablet');
        $result = $serializer->normalize($repository->findOneByIdentifier($tablet->getId()), 'standard');

        $this->assertSame($result, $expected);
    }
}
