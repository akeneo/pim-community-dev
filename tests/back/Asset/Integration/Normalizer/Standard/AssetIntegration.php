<?php

namespace AkeneoTestEnterprise\Asset\Integration\Normalizer\Standard;

use AkeneoTestEnterprise\Asset\Integration\Normalizer\Standard\AbstractStandardNormalizerTestCase;

class AssetIntegration extends AbstractStandardNormalizerTestCase
{
    public function testAsset()
    {
        $expected = [
            'code'        => 'cat',
            'localizable' => true,
            'description' => null,
            'end_of_use'  => '2041-04-02T00:00:00+01:00',
            'tags'        => ['animal'],
            'categories'  => ['asset_main_catalog']
        ];

        $repository = $this->get('pimee_product_asset.repository.asset');
        $serializer = $this->get('pim_standard_format_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier('cat'), 'standard');

        $this->assertSame($expected, $result);
    }
}
