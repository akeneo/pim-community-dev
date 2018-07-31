<?php

namespace Akeneo\Tool\Component\Api\tests\integration\Normalizer;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssociationTypeIntegration extends AbstractNormalizerTestCase
{
    public function testAssociationType()
    {
        $expected = [
            'code'   => 'X_SELL',
            'labels' => [
                'en_US' => 'Cross sell',
                'fr_FR' => 'Vente croisée',
            ],
        ];

        $repository = $this->get('pim_catalog.repository.association_type');
        $serializer = $this->get('pim_external_api_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier('X_SELL'), 'external_api');

        $this->assertEquals($expected, $result);
    }
}
