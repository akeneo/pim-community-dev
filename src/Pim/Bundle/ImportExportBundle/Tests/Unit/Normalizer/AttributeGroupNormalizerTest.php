<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\AttributeGroupNormalizer;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroupTranslation;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;

/**
 * Attribute group normalizer test
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeGroupNormalizer
     */
    protected $normalizer;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new AttributeGroupNormalizer();
    }

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\AttributeGroup', 'json',  true),
            array('Pim\Bundle\CatalogBundle\Entity\AttributeGroup', 'csv', false),
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
     * @static
     */
    public static function getNormalizeData()
    {
        return array(
            array(
                array(
                    'code'       => 'mycode',
                    'name'       => array('en_US' => 'My name', 'fr_FR' => 'Mon nom'),
                    'sortOrder'  => 5,
                    'attributes' => array('attribute1', 'attribute2', 'attribute3')
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
        $group = $this->createGroup();
        $this->assertEquals(
            $expectedResult,
            $this->normalizer->normalize($group, 'json')
        );
    }

    /**
     * Create attribute group
     *
     * @return AttributeGroup
     */
    protected function createGroup()
    {
        $group = new AttributeGroup();
        $group->setCode('mycode');

        $translations = array('en_US' => 'My name', 'fr_FR' => 'Mon nom');
        foreach ($translations as $locale => $label) {
            $translation = new AttributeGroupTranslation();
            $translation->setLocale($locale);
            $translation->setName($label);
            $group->addTranslation($translation);
        }

        $group->setSortOrder(5);

        $codes = array('attribute1', 'attribute2', 'attribute3');
        $attributes = array();
        foreach ($codes as $code) {
            $attribute = new ProductAttribute();
            $attribute->setCode($code);
            $group->addAttribute($attribute);
            $attributes[]= $attribute;
        }

        return $group;
    }
}
