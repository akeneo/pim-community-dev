<?php

namespace AkeneoTest\Pim\Structure\Integration\Normalizer\Versioning;

use Akeneo\Test\Integration\TestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeIntegration extends TestCase
{
    public function testAssociationType()
    {
        $associationType = $this->get('pim_catalog.repository.association_type')->findOneByIdentifier('X_SELL');
        $flatAssociationType = $this->get('pim_versioning.serializer')->normalize($associationType, 'flat');

        $this->assertSame($flatAssociationType, [
            'code'        => 'X_SELL',
            'label-en_US' => 'Cross sell',
            'label-fr_FR' => 'Vente croisée',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
