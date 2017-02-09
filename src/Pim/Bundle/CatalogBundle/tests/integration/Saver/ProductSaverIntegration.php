<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Saver;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductSaverIntegration extends TestCase
{
    /**
     * Be sure data has been deleted in database when it has been set to null
     */
    public function testRemoveNullableValue()
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');
        $standardProduct = $this->get('pim_serializer')->normalize($product, 'standard');

        $this->assertSame(
            $standardProduct['values']['a_metric'][0]['data'],
            ['amount' => '987654321987.1234', 'unit' => 'KILOWATT']
        );

        $standardProduct['values']['a_metric'][0]['data'] = null;
        $this->get('pim_catalog.updater.product')->update($product, $standardProduct);
        $this->get('pim_catalog.saver.product')->save($product);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');
        $standardProduct = $this->get('pim_serializer')->normalize($product, 'standard');

        $this->assertArrayNotHasKey('a_metric', $standardProduct['values']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalSqlCatalogPath()],
            false
        );
    }
}
