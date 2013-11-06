<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\GroupNormalizer;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CategoryNormalizer
     */
    protected $normalizer;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new GroupNormalizer();
    }

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\Group', 'json',  true),
            array('Pim\Bundle\CatalogBundle\Entity\Group', 'csv', false),
            array('stdClass',                              'json',  false),
            array('stdClass',                              'csv', false),
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
                    'code'        => 'my_variant_group',
                    'type'        => 'VARIANT',
                    'label-en_US' => 'My variant group',
                    'label-fr_FR' => 'Mon groupe variant',
                    'attributes'  => array('color', 'size')
                ),
                array(
                    'code'       => 'my_group',
                    'type'       => 'RELATED',
                    'label'      => array('en' => 'My group', 'fr' => 'Mon group'),
                    'attributes' => array()
                )
            ),
        );
    }

    /**
     * Test normalize method
     * @param array $data
     *
     * @dataProvider getNormalizeData
     */
    public function testNormalize(array $data)
    {
        $group = $this->createGroup($data);

        $this->assertEquals(
            $data,
            $this->normalizer->normalize($group, 'csv')
        );
    }

    /**
     * Create a group
     * @param array $data
     *
     * @return Group
     */
    protected function createGroup(array $data)
    {
        $group = new Group();
        $group->setCode($data['code']);

        $type = new GroupType();
        $type->setCode($data['type']);
        $type->setVariant(($data['type'] == 'VARIANT'));
        $group->setType($type);

        foreach ($this->getLabels($data) as $locale => $label) {
            $translation = $group->getTranslation($locale);
            $translation->setLabel($label);
            $group->addTranslation($translation);
        }

        foreach ($this->getAttributes($data) as $attribute) {
            $group->addAttribute($attribute);
        }

        return $group;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function getLabels($data)
    {
        return $data['label'];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function getAttributes($data)
    {
        $attributes = array();
        $codes = $data['attributes'];
        foreach ($codes as $code) {
            $attribute = new ProductAttribute();
            $attribute->setCode($code);
            $attributes[]= $attribute;
        }

        return $attributes;
    }
}
