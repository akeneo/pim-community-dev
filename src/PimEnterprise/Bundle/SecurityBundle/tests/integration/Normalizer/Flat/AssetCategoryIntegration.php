<?php

namespace PimEnterprise\Bundle\SecurityBundle\tests\integration\Normalizer\Flat;

class AssetCategoryIntegration extends AbstractFlatNormalizerTestCase
{
    public function testAssetCategory()
    {
        $assetCategory = $this->get('pimee_product_asset.repository.asset_category')
            ->findOneByIdentifier('asset_main_catalog');
        $flatAssetCategory = $this->get('pim_versioning.serializer')->normalize($assetCategory, 'flat');

        $this->assertSame($flatAssetCategory, [
            'code'            => 'asset_main_catalog',
            'parent'          => null,
            'label-en_US'     => 'Asset main catalog',
            'view_permission' => 'IT support,Manager,Redactor',
            'edit_permission' => 'IT support,Manager'
        ]);
    }
}
