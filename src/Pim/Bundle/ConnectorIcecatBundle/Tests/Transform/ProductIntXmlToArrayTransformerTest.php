<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Transform;

use Pim\Bundle\ConnectorIcecatBundle\Transform\ProductIntXmlToArrayTransformer;

use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
/**
 * Test ProductIntXmlToArrayTransformer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductIntXmlToArrayTransformerTest extends KernelAwareTest
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
        $this->assertProductId10($resultArray['basedata']);
        $this->assertCategory($resultArray['category'], 234);
        $this->assertCategoryFeatureGroups($resultArray['categoryfeaturegroups'], 31);
        $this->assertProductFeatures($resultArray['productfeatures'], 57);
    }

    /**
     * Test loading product when product id unexistent (file 26271.xml)
     */
    public function testProductNotPresent()
    {
        try {
            $filename = '26271.xml';
            $resultArray = $this->loadFile($filename);
        } catch (\Exception $e) {
            $this->assertEquals('Pim\Bundle\ConnectorIcecatBundle\Exception\TransformException', get_class($e));
        }
    }

    /**
     * Load a file in SimpleXmlElement format
     * @param string $filename
     *
     * @return \SimpleXMLElement
     */
    protected function loadFile($filename)
    {
        $filepath = dirname(__FILE__) .'/../Files/'. $filename;
        $content = simplexml_load_file($filepath);

        // call transformer
        $transformer = new ProductIntXmlToArrayTransformer($content);

        return $transformer->transform();
    }

    /**
     * Test to extract xml data to array
     * @param array $resultArray
     */
    protected function assertTransformXmlToArray($resultArray)
    {
        // global array assertions
        $this->assertCount(4, $resultArray);
        $this->assertArrayHasKey('basedata', $resultArray);
        $this->assertArrayHasKey('category', $resultArray);
        $this->assertArrayHasKey('categoryfeaturegroups', $resultArray);
        $this->assertArrayHasKey('productfeatures', $resultArray);
    }

    /**
     * Assert base data for products
     * @param array $baseData
     */
    protected function assertProductId10($baseData)
    {
        // assert product data
        $this->assertValue('id', 'C8934A#A2L', $baseData);
        $this->assertValue('name', 'deskjet 845c printer', $baseData);
        $this->assertValue('HighPic', 'http://images.icecat.biz/img/norm/high/1317.jpg', $baseData);
        $this->assertValue('LowPic', 'http://images.icecat.biz/img/norm/low/1317.jpg', $baseData);
        $this->assertValue('ThumbPic', 'http://images.icecat.biz/thumbs/1317.jpg', $baseData);

        // assert vendor data
        $this->assertValue('vendorId', 1, $baseData);
        $this->assertValue('vendorName', 'HP', $baseData);

        // assert summary description data
        $this->assertArrayHasKey('ShortDescription', $baseData);
        $this->assertArrayHasKey('LongDescription', $baseData);
    }

    /**
     * Assert category data of the product
     * @param array   $category   category array for i18n
     * @param integer $categoryId Id of product category
     */
    protected function assertCategory($category, $categoryId)
    {
        $this->assertValue('id', $categoryId, $category);
        $this->assertI18N($category['name']);
    }

    /**
     * Assert group data
     * @param array   $groups groups array
     * @param integer $count  number of groups expected
     */
    protected function assertCategoryFeatureGroups($groups, $count)
    {
        $this->assertCount($count, $groups);
        foreach ($groups as $group) {
            $this->assertI18N($group);
        }
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
            $this->assertArrayHasKey('CategoryFeatureGroup_ID', $feature);
            $this->assertArrayHasKey('Value', $feature);
            $this->assertI18N($feature['Value']);
        }
    }

    /**
     * Assert key existing and value
     * @param string $key   Key in the associative array
     * @param mixed  $value Value tested
     * @param array  $array Array concerning by the assertion
     */
    protected function assertValue($key, $value, $array)
    {
        $this->assertArrayHasKey($key, $array);
        $this->assertEquals($value, $array[$key]);
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
