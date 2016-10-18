<?php

namespace tests\integration\PimEnterprise\Component\ProductAsset\Normalizer\Standard;

use TestEnterprise\Integration\TestCase;

class AssetIntegration extends TestCase
{
    public function testAsset()
    {
        $expected = [
            'code'        => 'cat',
            'localized'   => true,
            'description' => null,
            'end_of_use'  => '2041-04-02T00:00:00+01:00',
            'tags'        => ['animal'],
            'categories'  => ['asset_main_catalog']
        ];

        $repository = $this->get('pimee_product_asset.repository.asset');
        $serializer = $this->get('pim_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier('cat'), 'standard');

        $this->assertSame($result, $expected);
    }
}
