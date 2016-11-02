<?php

namespace tests\integration\PimEnterprise\Bundle\SecurityBundle\Normalizer\Flat;

use TestEnterprise\Integration\TestCase;

class LocaleIntegration extends TestCase
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
