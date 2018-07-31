<?php

namespace Akeneo\Tool\Component\Api\tests\integration\Normalizer;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CurrencyIntegration extends AbstractNormalizerTestCase
{
    public function testCurrency()
    {
        $expected = [
            'code'    => 'EUR',
            'enabled' => true,
        ];

        $repository = $this->get('pim_catalog.repository.currency');
        $serializer = $this->get('pim_external_api_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier('EUR'), 'external_api');

        $this->assertEquals($expected, $result);
    }
}
