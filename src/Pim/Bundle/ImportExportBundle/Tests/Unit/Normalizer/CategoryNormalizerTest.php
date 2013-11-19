<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\CategoryNormalizer;
use Pim\Bundle\CatalogBundle\Entity\Category;

/**
 * Test class for CategoryNormalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizerTest extends NormalizerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new CategoryNormalizer();
        $this->format     = 'json';
    }

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Model\CategoryInterface', 'json',  true),
            array('Pim\Bundle\CatalogBundle\Model\CategoryInterface', 'csv', false),
            array('stdClass',                                         'json',  false),
            array('stdClass',                                         'csv', false),
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
                    'label'   => array('en' => 'Root category', 'fr' => 'Categorie racine'),
                    'parent'  => '',
                    'dynamic' => '0',
                )
            ),
            array(
                array(
                    'code'    => 'child_category',
                    'label'   => array('en' => 'Child category', 'fr' => 'fr:Catégorie enfant'),
                    'parent'  => '1',
                    'dynamic' => '0',
                )
            ),
        );
    }

    /**
     * Create a category
     * @param array $data
     *
     * @return Category
     */
    protected function createEntity(array $data)
    {
        $category = new Category();
        $category->setCode($data['code']);

        foreach ($this->getLabels($data) as $locale => $label) {
            $translation = $category->getTranslation($locale);
            $translation->setLabel($label);
            $category->addTranslation($translation);
        }

        if ($data['parent']) {
            $parent = new Category();
            $parent->setCode($data['parent']);
            $category->setParent($parent);
        }

        $category->setDynamic($data['dynamic']);

        return $category;
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
}
