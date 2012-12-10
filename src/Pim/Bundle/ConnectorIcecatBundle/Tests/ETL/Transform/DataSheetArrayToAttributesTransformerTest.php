<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Transform;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\DataSheetArrayToAttributesTransformer;

use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\ProductSetXmlToDataSheetTransformer;

use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class DataSheetArrayToAttributesTransformerTest extends KernelAwareTest
{
    /**
     * @staticvar integer
     */
    const LANGS_COUNT = 2;

    /**
     * Xml content tested
     * @var string
     */
    protected $fileContent;

    /**
     * Test data import with product id 10
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
        $transformer = new DataSheetArrayToAttributesTransformer($productManager, $datasheet);
        $attributes = $transformer->transform();

        // assertions
        $this->assertCount(57, $attributes);
        foreach ($attributes as $attribute) {
            $this->assertInstanceOfProductAttribute($attribute);
        }
    }

    /**
     * Load a file in SimpleXmlElement format
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
     * Assert entity is a ProductAttribute entity
     * @param object $entity
     */
    protected function assertInstanceOfProductAttribute($entity)
    {
        $this->assertInstanceOf('\Pim\Bundle\CatalogBundle\Entity\ProductAttribute', $entity);
    }
}
