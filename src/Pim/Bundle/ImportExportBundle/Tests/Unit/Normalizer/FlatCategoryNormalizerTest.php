<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\FlatCategoryNormalizer;
use Pim\Bundle\CatalogBundle\Entity\Category;

/**
 * Test class for CategoryNormalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatCategoryNormalizerTest extends CategoryNormalizerTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new FlatCategoryNormalizer();
    }

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Model\CategoryInterface', 'csv',  true),
            array('Pim\Bundle\CatalogBundle\Model\CategoryInterface', 'json', false),
            array('stdClass',                                         'csv',  false),
            array('stdClass',                                         'json', false),
        );
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
                    'title'   => 'en:Root category,fr:Categorie racine',
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
                    'title'   => 'en:Child category,fr:Catégorie enfant',
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
     * @param array $expected
     *
     * @dataProvider getNormalizeData
     */
    public function testNormalize(array $expected)
    {
        $category = $this->createCategory($expected);
        $result = $this->normalizer->normalize($category, 'csv');
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    protected function getTitles($data)
    {
        $titles = array();
        foreach (explode(',', $data['title']) as $data) {
            $title = explode(':', $data);
            $locale = reset($title);
            $title = end($title);
            $titles[$locale]= $title;
        }

        return $titles;
    }
}
