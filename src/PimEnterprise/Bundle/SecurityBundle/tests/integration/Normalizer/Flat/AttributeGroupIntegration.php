<?php

namespace PimEnterprise\Bundle\SecurityBundle\tests\integration\Normalizer\Flat;

use Akeneo\TestEnterprise\Integration\PermissionCleaner;

class AttributeGroupIntegration extends AbstractFlatNormalizerTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $configuration = $this->getConfiguration();
        if ($configuration->isDatabasePurgedForEachTest() || 1 === self::$count) {
            $permissionCleaner = new PermissionCleaner(static::$kernel);
            $permissionCleaner->cleanPermission(static::$kernel);
        }
    }

    public function testAttributeGroup()
    {
        $attributeGroup = $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier('attributeGroupA');
        $flatAttributeGroup = $this->get('pim_versioning.serializer')->normalize($attributeGroup, 'flat');

        $this->assertSame([
            'code'            => 'attributeGroupA',
            'sort_order'      => 1,
            'attributes'      => 'sku,a_date,a_file,a_price,a_price_without_decimal,a_ref_data_multi_select,a_ref_data_simple_select,a_text,a_regexp,a_text_area,a_yes_no,a_scopable_price,a_localized_and_scopable_text_area',
            'label-en_US'     => 'Attribute group A',
            'label-fr_FR'     => 'Groupe d\'attribut A',
            'view_permission' => 'IT support,Manager',
            'edit_permission' => 'IT support,Manager'
        ], $flatAttributeGroup);
    }
}
