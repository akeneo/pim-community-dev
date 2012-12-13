<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Transform;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\DataSheetArrayToSetTransformer;

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
class DataSheetArrayToSetTransformerTest extends KernelAwareTest
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
        $productManager    = $this->container->get('pim.catalog.product_manager');
        $productTplManager = $this->container->get('pim.catalog.product_template_manager');

        // call transformer
        $transformer = new DataSheetArrayToSetTransformer($productManager, $productTplManager, $datasheet);
        $set = $transformer->transform();

        // assertions
        $this->assertInstanceOfProductSet($set);
        $this->equals('', $set->getCode());
        $this->equals('', $set->getTitle());
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
     * Assert entity is a ProductSet entity
     * @param object $entity
     */
    protected function assertInstanceOfProductSet($entity)
    {
        $this->assertInstanceOf('\Pim\Bundle\CatalogBundle\Doctrine\ProductTemplateManager', $entity);
    }
}
