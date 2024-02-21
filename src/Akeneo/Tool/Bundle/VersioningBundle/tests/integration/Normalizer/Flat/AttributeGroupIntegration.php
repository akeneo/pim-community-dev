<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\tests\integration\Normalizer\Flat;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @group ce
 */
class AttributeGroupIntegration extends AbstractFlatNormalizerTestCase
{
    public function testAttributeGroup()
    {
        $attributeGroup = $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier('attributeGroupA');
        $flatAttributeGroup = $this->get('pim_versioning.serializer')->normalize($attributeGroup, 'flat');

        $this->assertSame($flatAttributeGroup, [
            'code'        => 'attributeGroupA',
            'sort_order'  => 1,
            'attributes'  => 'sku,a_date,a_file,an_image,a_price,a_price_without_decimal,a_ref_data_multi_select,a_ref_data_simple_select,a_text,a_regexp,a_text_area,a_yes_no,a_scopable_price,a_localized_and_scopable_text_area',
            'label-en_US' => 'Attribute group A',
            'label-fr_FR' => 'Groupe d\'attribut A',
        ]);
    }
}
