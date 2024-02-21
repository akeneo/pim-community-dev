<?php

namespace Akeneo\Tool\Component\Api\tests\integration\Normalizer;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleNormalizerIntegration extends AbstractNormalizerTestCase
{
    public function testLocaleNormalization()
    {
        $expected = [
            'code'    => 'en_US',
            'enabled' => true
        ];

        $this->assert('en_US', $expected);
    }

    /**
     * @param string $localeCode
     * @param array  $expected
     */
    private function assert($localeCode, array $expected)
    {
        $repository = $this->get('pim_catalog.repository.locale');
        $serializer = $this->get('pim_external_api_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier($localeCode), 'external_api');

        $this->assertSame($expected, $result);
    }
}
