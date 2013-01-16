<?php
namespace Pim\Bundle\FlexibleProductBundle\Tests\Entity;

use Pim\Bundle\TestBundle\Tests\KernelAwareTest;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ProductTest extends KernelAwareTest
{

    /**
     * Sku value
     * @staticvar string
     */
    protected static $productSku = 'product-sku';

    /**
     * Test getter and setter for sku
     */
    public function testSku()
    {
        // create product entity
        $product = $this->getProductManager()->createEntity();

        // assert default value for sku value
        $this->assertNull($product->getSku());

        // set a sku value to product
        $product->setSku(self::$productSku);
        $this->assertEquals(self::$productSku, $product->getSku());
    }

    /**
     * Get product manager
     * @return Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleEntityManager
     */
    protected function getProductManager()
    {
        return $this->container->get('pim.flexible_product.product_manager');
    }
}