<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Normalizer\Flat;

use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedCategoryCleaner;

class CategoryIntegration extends AbstractFlatNormalizerTestCase
{
    public function testAssetCategory()
    {
        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('master');
        $flatCategory = $this->get('pim_versioning.serializer')->normalize($category, 'flat');

        $expectedCategory = [
            'code'            => 'master',
            'parent'          => null,
            'updated'         => '2021-05-12T16:32:25+02:00',
            'view_permission' => 'IT support,Manager,Redactor',
            'edit_permission' => 'IT support,Manager,Redactor',
            'own_permission'  => 'IT support,Manager,Redactor'
        ];

        NormalizedCategoryCleaner::clean($expectedCategory);
        NormalizedCategoryCleaner::clean($flatCategory);

        $this->assertSame($flatCategory, $expectedCategory);
    }
}
