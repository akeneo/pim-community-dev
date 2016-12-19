<?php

namespace tests\integration\PimEnterprise\Component\ProductAsset\Normalizer\Standard;

use TestEnterprise\Integration\TestCase;

class ChannelConfigurationIntegration extends TestCase
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
        $serializer = $this->get('pim_serializer');

        $tablet = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('tablet');
        $result = $serializer->normalize($repository->findOneByIdentifier($tablet->getId()), 'standard');

        $this->assertSame($result, $expected);
    }
}
