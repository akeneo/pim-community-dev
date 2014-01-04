<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\AttributeOptionNormalizer;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;

/**
 * Attribute option normalizer test
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionNormalizerTest extends NormalizerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new AttributeOptionNormalizer();
        $this->format     = 'json';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\AttributeOption', 'json',  true),
            array('Pim\Bundle\CatalogBundle\Entity\AttributeOption', 'xml', true),
            array('Pim\Bundle\CatalogBundle\Entity\AttributeOption', 'csv', false),
            array('stdClass', 'json',  false),
            array('stdClass', 'xml',  false),
            array('stdClass', 'csv', false),
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
                    'attribute'  => 'color',
                    'code'       => 'red',
                    'default' => 0,
                    'label' => array('en_US' => 'Red', 'fr_FR' => 'Rouge')
                )
            ),
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return AttributeOption
     */
    protected function createEntity(array $data)
    {
        $attribute = new Attribute();
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
