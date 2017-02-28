<?php

namespace PimEnterprise\Bundle\SecurityBundle\tests\integration\Normalizer\Flat;

class CategoryIntegration extends AbstractFlatNormalizerTestCase
{
    public function testAssetCategory()
    {
        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier('master');
        $flatCategory = $this->get('pim_versioning.serializer')->normalize($category, 'flat');

        $this->assertSame($flatCategory, [
            'code'            => 'master',
            'parent'          => null,
            'view_permission' => 'IT support,Manager,Redactor',
            'edit_permission' => 'IT support,Manager,Redactor',
            'own_permission'  => 'IT support,Manager,Redactor'
        ]);
    }
}
