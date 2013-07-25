<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\CategoryNormalizer;
use Pim\Bundle\ProductBundle\Entity\Category;

/**
 * Test class for CategoryNormalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizerTest extends \PHPUnit_Framework_TestCase
{
    private $normalizer;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new CategoryNormalizer();
    }

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\ProductBundle\Model\CategoryInterface', 'csv',  true),
            array('Pim\Bundle\ProductBundle\Model\CategoryInterface', 'json', false),
            array('stdClass',                                         'csv',  false),
            array('stdClass',                                         'json', false),
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
                    'code'    => 'root_category',
                    'name_en' => 'Root category',
                    'name_fr' => 'Categorie racine',
                    'parent'  => '',
                    'dynamic' => '0',
                    'left'    => '1',
                    'level'   => '0',
                    'right'   => '2'
                )
            ),
            array(
                array(
                    'code'    => 'child_category',
                    'name_en' => 'Child category',
                    'name_fr' => 'Categorie enfante',
                    'parent'  => '1',
                    'dynamic' => '0',
                    'left'    => '2',
                    'level'   => '1',
                    'right'   => '3'
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
    public function testNormalize(array $data = array())
    {
        $category = $this->createCategory($data);

        $this->assertEquals(
            $data,
            $this->normalizer->normalize($category, 'csv')
        );
    }

    /**
     * Create a category
     * @param array $data
     *
     * @return Category
     */
    private function createCategory(array $data = array())
    {
        $category = new Category();
        $category->setCode($data['code']);

        $titles = array_filter(
            array_keys($data),
            function ($item) {
                return strpos($item, 'name') !== false;
            }
        );

        foreach ($titles as $title) {
            $locale = end(explode('_', $title));
            $translation = $category->getTranslation($locale);
            $translation->setTitle($data[$title]);
        }

        if ($data['parent']) {
            $parent = new Category();
            $parent->setCode($data['parent']);
            $category->setParent($parent);
        }

        $category->setDynamic($data['dynamic']);
        $category->setLeft($data['left']);
        $category->setLevel($data['level']);
        $category->setRight($data['right']);

        return $category;
    }
}
