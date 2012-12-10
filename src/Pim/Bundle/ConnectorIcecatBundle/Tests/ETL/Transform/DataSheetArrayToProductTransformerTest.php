<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Transform;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\DataSheetArrayToProductTransformer;

use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;

use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class DataSheetArrayToProductTransformerTest extends KernelAwareTest
{
    /**
     * Test related method
     */
    public function testTransform()
    {
        // load json content
        $filename = 'product-data-sheet-10.json';
        $jsonContent = $this->loadFile($filename);

        // create datasheet
        $datasheet = new IcecatProductDataSheet();
        $datasheet->setData($jsonContent);

        // initialize productManager
        $productManager = $this->container->get('pim.catalog.product_manager');

        // call transformer
        $transformer = new DataSheetArrayToProductTransformer($productManager, $datasheet);
        $product = $transformer->transform();

        // assertions
        $this->assertInstanceOfProduct($product);
        $this->assertEquals('C8934A#A2L', $product->getSku());
    }

    /**
     * Load content file
     * @param string $filename
     *
     * @return string
     */
    protected function loadFile($filename)
    {
        $filepath = dirname(__FILE__) .'/../../Files/'. $filename;

        return file_get_contents($filepath);
    }

    /**
     * Assert entity is a Product entity
     * @param object $entity
     */
    protected function assertInstanceOfProduct($entity)
    {
        $this->assertInstanceOf('\Oro\Bundle\FlexibleEntityBundle\Entity\Entity', $entity);
    }
}
