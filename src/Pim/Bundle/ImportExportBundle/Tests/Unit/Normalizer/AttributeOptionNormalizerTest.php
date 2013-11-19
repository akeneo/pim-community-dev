<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\AttributeOptionNormalizer;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;

/**
 * Attribute option normalizer test
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeOptionNormalizer
     */
    protected $normalizer;

    /**
     * @var string
     */
    protected $format = 'json';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new AttributeOptionNormalizer();
    }

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\AttributeOption', 'json',  true),
            array('Pim\Bundle\CatalogBundle\Entity\AttributeOption', 'csv', false),
            array('stdClass', 'json',  false),
            array('stdClass', 'csv', false),
        );
    }

    /**
     * Test supportsNormalization method
     * @param mixed   $class
     * @param string  $format
     * @param boolean $isSupported
     *
     * @dataProvider getSupportNormalizationData
     */
    public function testSupportNormalization($class, $format, $isSupported)
    {
        $data = $this->getMock($class);

        $this->assertSame($isSupported, $this->normalizer->supportsNormalization($data, $format));
    }

    /**
     * Data provider for testing normalize method
     * @return array
     */
    public static function getNormalizeData()
    {
        return array(
            array(
                array(
                    'attribute'  => 'color',
                    'code'       => 'red',
                    'is_default' => 0,
                    'label' => array('en_US' => 'Red', 'fr_FR' => 'Rouge')
                )
            ),
        );
    }

    /**
     * Test normalize method
     * @param array $expectedResult
     *
     * @dataProvider getNormalizeData
     */
    public function testNormalize(array $expectedResult)
    {
        $option = $this->createAttributeOption($expectedResult);
        $this->assertEquals(
            $expectedResult,
            $this->normalizer->normalize($option, $this->format)
        );
    }

    /**
     * Create an attribute option
     * @param array $data
     *
     * @return AttributeOption
     */
    protected function createAttributeOption(array $data)
    {
        $attribute = new ProductAttribute();
        $attribute->setCode($data['attribute']);

        $option = new AttributeOption();
        $option->setCode($data['code']);
        $option->setAttribute($attribute);

        $this->addAttributeOptionLabels($option, $data);

        return $option;
    }

    /**
     * Add attribute option labels
     *
     * @param AttributeOption $option
     * @param array           $data
     */
    protected function addAttributeOptionLabels(AttributeOption $option, array $data)
    {
        foreach ($data['label'] as $locale => $data) {
            $value = new AttributeOptionValue();
            $value->setLocale($locale);
            $value->setValue($data);
            $option->addOptionValue($value);
        }
    }
}
