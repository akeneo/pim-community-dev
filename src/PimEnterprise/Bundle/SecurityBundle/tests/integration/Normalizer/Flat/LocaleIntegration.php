<?php

namespace PimEnterprise\Bundle\SecurityBundle\tests\integration\Normalizer\Flat;

class LocaleIntegration extends AbstractFlatNormalizerTestCase
{
    public function testAssetLocale()
    {
        $locale = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('de_DE');
        $flatLocale = $this->get('pim_versioning.serializer')->normalize($locale, 'flat');

        $this->assertSame($flatLocale, [
            'code'            => 'de_DE',
            'view_permission' => 'All',
            'edit_permission' => 'All'
        ]);
    }
}
