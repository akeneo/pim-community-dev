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
     * @staticvar string
     */
    const FILEPATH = 'DataFixtures/Tests/Files/';

    /**
     * @staticvar string
     */
    const FILENAME = 'detailled-product.xml';

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
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $content = file_get_contents(dirname(__FILE__) .'/../../'. self::FILEPATH . self::FILENAME);
        $this->fileContent = simplexml_load_string($content);
    }

    /**
     * Test to extract xml data to array
     */
    public function testTransformXmlToArray()
    {
        // call transformer
        $transformer = new ProductIntXmlToArrayTransformer($this->fileContent);
        $resultArray = $transformer->transform();

        // global array assertions
        $this->assertCount(4, $resultArray);
        $this->assertArrayHasKey('basedata', $resultArray);
        $this->assertArrayHasKey('category', $resultArray);
        $this->assertArrayHasKey('categoryfeaturegroups', $resultArray);
        $this->assertArrayHasKey('productfeatures', $resultArray);

        // assertions for each part of the global array
        $this->assertBaseData($resultArray['basedata']);
        $this->assertCategory($resultArray['category']);
        $this->assertCategoryFeatureGroups($resultArray['categoryfeaturegroups']);
        $this->assertProductFeatures($resultArray['productfeatures']);
    }

    /**
     * Assert base data for products
     * @param array $baseData
     */
    protected function assertBaseData($baseData)
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
     * @param array $category
     */
    protected function assertCategory($category)
    {
        $this->assertValue('id', 234, $category);
        $this->assertI18N($category['name']);
    }

    /**
     * Assert group data
     * @param array $groups
     */
    protected function assertCategoryFeatureGroups($groups)
    {
        $this->assertCount(31, $groups);
        foreach ($groups as $group) {
            $this->assertI18N($group);
        }
    }

    /**
     * Assert features data
     * @param array $features
     */
    protected function assertProductFeatures($features)
    {
        $this->assertCount(57, $features);
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
