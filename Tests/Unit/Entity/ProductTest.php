<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Entity;

use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ProductBundle\Entity\ProductFamily;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $product = new Product();
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\Product', $product);
    }

    /**
     * Test getter/setter for sku property
     */
    public function testGetSetSku()
    {
        $product = new Product();
        $this->assertEmpty($product->getSku());

        // Change value and assert new
        $newSku = 'test-sku';
        $product->setSku($newSku);
        $this->assertEquals($newSku, $product->getSku());
    }

    /**
     * Test getter/setter for productFamily property
     */
    public function testGetSetProductFamily()
    {
        $product = new Product();
        $this->assertEmpty($product->getProductFamily());

        // Change value and assert new
        $newProductFamily = new ProductFamily();
        $product->setProductFamily($newProductFamily);
        $this->assertEquals($newProductFamily, $product->getProductFamily());

        $product->setProductFamily(null);
        $this->assertNull($product->getProductFamily());
    }
}
