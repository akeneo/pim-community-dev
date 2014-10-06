<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Normalizer\Flat;

use Pim\Bundle\TransformBundle\Normalizer\Flat\CategoryNormalizer;
use Pim\Bundle\TransformBundle\Normalizer\Flat\TranslationNormalizer;
use Pim\Bundle\TransformBundle\Tests\Unit\Normalizer\Structured;

/**
 * Test class for CategoryNormalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizerTest extends Structured\CategoryNormalizerTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new CategoryNormalizer(new TranslationNormalizer());
        $this->format     = 'csv';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Model\CategoryInterface', 'csv', true),
            array('Pim\Bundle\CatalogBundle\Model\CategoryInterface', 'xml', false),
            array('Pim\Bundle\CatalogBundle\Model\CategoryInterface', 'json', false),
            array('Pim\Bundle\CatalogBundle\Entity\Category', 'csv', true),
            array('Pim\Bundle\CatalogBundle\Entity\Category', 'xml', false),
            array('Pim\Bundle\CatalogBundle\Entity\Category', 'json', false),
            array('stdClass', 'csv', false),
            array('stdClass', 'xml', false),
            array('stdClass', 'json', false),
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getNormalizeData()
    {
        return array(
            array(
                array(
                    'code'        => 'root_category',
                    'label-en_US' => 'Root category',
                    'label-fr_FR' => 'Categorie racine',
                    'parent'      => ''
                )
            ),
            array(
                array(
                    'code'        => 'child_category',
                    'label-en_US' => 'Root category',
                    'label-fr_FR' => 'Categorie racine',
                    'parent'      => '1'
                )
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getLabels($data)
    {
        return array(
            'en_US' => $data['label-en_US'],
            'fr_FR' => $data['label-fr_FR']
        );
    }
}
