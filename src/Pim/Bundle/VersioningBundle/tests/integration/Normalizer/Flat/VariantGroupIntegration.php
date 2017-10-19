<?php

namespace tests\integration\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Bundle\VersioningBundle\tests\integration\Normalizer\Flat\AbstractFlatNormalizerTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupIntegration extends AbstractFlatNormalizerTestCase
{
    public function testVariantGroup()
    {
        $variantGroup = $this->get('pim_catalog.repository.group')->findOneByIdentifier('variantA');

        $this->get('pim_serializer');
        $flatVariantGroup = $this->get('pim_versioning.serializer')->normalize($variantGroup, 'flat');

        $this->assertSame($flatVariantGroup, [
            'code'        => 'variantA',
            'type'        => 'VARIANT',
            'axis'        => 'a_simple_select',
            'a_text'      => 'A name',
            'label-fr_FR' => 'Variante A',
            'label-en_US' => 'Variant A'
        ]);
    }
}
