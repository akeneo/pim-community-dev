<?php

namespace Akeneo\Tool\Component\Api\tests\integration\Normalizer;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelNormalizerIntegration extends AbstractNormalizerTestCase
{
    public function testChannelNormalization()
    {
        $expected = [
            'code'             => 'ecommerce',
            'currencies'       => ['USD', 'EUR'],
            'locales'          => ['en_US'],
            'category_tree'    => 'master',
            'conversion_units' => [
                'a_metric_without_decimal' => 'METER',
                'a_metric'                 => 'KILOWATT',
            ],
            'labels'           => [
                'en_US' => 'Ecommerce',
                'fr_FR' => 'Ecommerce',
            ],
        ];

        $this->assert('ecommerce', $expected);
    }

    /**
     * @param string $channelCode
     * @param array  $expected
     */
    private function assert($channelCode, array $expected)
    {
        $repository = $this->get('pim_catalog.repository.channel');
        $serializer = $this->get('pim_external_api_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier($channelCode), 'external_api');

        $this->assertEquals($expected, $result);
    }
}
