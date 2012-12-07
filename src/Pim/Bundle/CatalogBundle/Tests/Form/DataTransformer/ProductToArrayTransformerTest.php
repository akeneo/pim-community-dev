<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;
use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
use Pim\Bundle\CatalogBundle\Form\DataTransformer\ProductToArrayTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductToArrayTransformerTest extends KernelAwareTest
{

    /**
     * Test related method
     */
    public function testTransform()
    {
        // get first
        $productManager = $this->container->get('pim.catalog.product_manager');
        $product = $productManager->getEntityRepository()->findOneBy(array());
        $this->assertNotNull($product);

        // transform to array
        $transformer = new ProductToArrayTransformer($productManager);
        $data = $transformer->transform($product);

        // assert
        $this->assertCompareArrayAndProduct($data, $product);
    }

    /**
     * Test related method
     */
    public function testReverseTransform()
    {
        // get first
        $productManager = $this->container->get('pim.catalog.product_manager');
        $product = $productManager->getEntityRepository()->findOneBy(array());
        $this->assertNotNull($product);

        // transform to array
        $transformer = new ProductToArrayTransformer($productManager);
        $data = $transformer->transform($product);

        // add a value for attribute
        $othersAttributes = $productManager->getAttributeRepository()->findAll();
        foreach ($othersAttributes as $att) {
            // add only one
            $data['values'][$att->getCode()]= 'my value';
            break;
        }

        // transform to product
        $productFromData = $transformer->reverseTransform($data);

        // assert
        $this->assertCompareArrayAndProduct($data, $productFromData);
    }

    /**
     * Test related method
     */
    public function testReverseTransformException()
    {
        // get first
//         $productManager = $this->container->get('pim.catalog.product_manager');
//         $transformer = new ProductToArrayTransformer($productManager);

//         // product not exists
//         $data = array();
//         $data['id'] = null;
//         try {
//             $productFromData = $transformer->reverseTransform($data);
//         } catch (TransformationFailedException $e) {
//             return;
//         }
//         $this->fail('An expected exception has not been raised.');
    }

    /**
     * refactor asserts fot transform and reverse
     *
     * @param array   $data    product data
     * @param Product $product entity
     */
    protected function assertCompareArrayAndProduct($data, $product)
    {
        // base data
        $this->assertEquals($product->getId(), $data['id']);
        // groups
        $this->assertEquals($product->getValues()->count(), count($data['values']));
        foreach ($product->getValues() as $value) {
            $this->assertTrue(isset($data['values'][$value->getAttribute()->getCode()]));
            $this->assertEquals($value->getData(), $data['values'][$value->getAttribute()->getCode()]);
        }
    }

}
