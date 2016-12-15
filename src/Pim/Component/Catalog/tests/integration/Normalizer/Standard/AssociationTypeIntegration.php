<?php

namespace tests\integration\Pim\Component\Catalog\Standard;

use Test\Integration\TestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeIntegration extends TestCase
{
    public function testAssociationType()
    {
        $expected = [
            'code'   => 'SUBSTITUTION',
            'labels' => [
                'en_US' => 'Substitution',
                'fr_FR' => 'Remplacement'
            ]
        ];

        $repository = $this->get('pim_catalog.repository.association_type');
        $serializer = $this->get('pim_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier('SUBSTITUTION'), 'standard');

        $this->assertSame($expected, $result);
    }
}
