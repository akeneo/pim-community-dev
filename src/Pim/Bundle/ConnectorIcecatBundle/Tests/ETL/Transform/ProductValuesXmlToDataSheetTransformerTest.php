<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Transform;

use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\ProductValuesXmlToDataSheetTransformer;

use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
/**
 * Test ProductSetXmlToDataSheetTransformer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductValuesXmlToDataSheetTransformerTest extends KernelAwareTest
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
    public function testProductId10()
    {
        $filename = '10.xml';
        $resultArray = $this->loadFile($filename);

        // assert global data
        $this->assertTransformXmlToArray($resultArray);

        // assertions for each part of the global array
        $this->assertProductFeatures($resultArray['productfeatures'], 57);
    }

    /**
     * Test loading product when product id unexistent (file 26271.xml)
     */
    public function testProductNotPresent()
    {
//         try {
//             $filename = '26271.xml';
//             $resultArray = $this->loadFile($filename);
//         } catch (\Exception $e) {
//             $this->assertEquals('Pim\Bundle\ConnectorIcecatBundle\Exception\TransformException', get_class($e));
//         }
    }

    /**
     * Load a file in SimpleXmlElement format
     * @param string $filename
     *
     * @return \SimpleXMLElement
     */
    protected function loadFile($filename)
    {
        $filepath = dirname(__FILE__) .'/../../Files/'. $filename;
        $content = simplexml_load_file($filepath);

        $datasheet = new IcecatProductDataSheet();

        // call transformer
        $transformer = new ProductValuesXmlToDataSheetTransformer($content, $datasheet, 1);
        $transformer->enrich();

        return json_decode($datasheet->getData(), true);
    }

    /**
     * Test to extract xml data to array
     * @param array $resultArray
     */
    protected function assertTransformXmlToArray($resultArray)
    {
        $this->assertCount(1, $resultArray);
        $this->assertArrayHasKey('productfeatures', $resultArray);
    }

    /**
     * Assert features data
     * @param array   $features features array
     * @param integer $count    number of features expected
     */
    protected function assertProductFeatures($features, $count)
    {
        $this->assertCount($count, $features);
        foreach ($features as $feature) {
            $this->assertArrayHasKey('Name', $feature);
//             $this->assertI18N($feature['Name']);
        }
    }

    /**
     * Assert I18N values
     * @param array $values
     */
    protected function assertI18N($values)
    {
        $this->assertCount(self::LANGS_COUNT, $values);
        $this->assertArrayHasKey(1, $values);
        $this->assertArrayHasKey(3, $values);
    }
}
