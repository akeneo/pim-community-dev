<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Normalizer\Flat;

use Pim\Bundle\TransformBundle\Normalizer\Flat\AttributeGroupNormalizer;
use Pim\Bundle\TransformBundle\Normalizer\Flat\TranslationNormalizer;
use Pim\Bundle\TransformBundle\Tests\Unit\Normalizer\Structured;

/**
 * Attribute group flat normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizerTest extends Structured\AttributeGroupNormalizerTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new AttributeGroupNormalizer(new TranslationNormalizer());
        $this->format     = 'csv';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\AttributeGroup', 'csv', true),
            array('Pim\Bundle\CatalogBundle\Entity\AttributeGroup', 'xml', false),
            array('Pim\Bundle\CatalogBundle\Entity\AttributeGroup', 'json', false),
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
                    'code'       => 'mycode',
                    'label-en_US' => 'My name',
                    'label-fr_FR' => 'Mon nom',
                    'sortOrder'  => 5,
                    'attributes' => 'attribute1,attribute2,attribute3'
                )
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity(array $data)
    {
        $data['attributes'] = explode(',', $data['attributes']);

        return parent::createEntity($data);
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
