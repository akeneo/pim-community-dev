<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\FlatAttributeOptionNormalizer;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;

/**
 * Flat attribute option normalizer test
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatAttributeOptionNormalizerTest extends AttributeOptionNormalizerTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new FlatAttributeOptionNormalizer();
        $this->format     = 'csv';
    }

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\AttributeOption', 'json', false),
            array('Pim\Bundle\CatalogBundle\Entity\AttributeOption', 'csv', true),
            array('stdClass', 'json',  false),
            array('stdClass', 'csv', false),
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
                    'attribute'   => 'color',
                    'code'        => 'red',
                    'is_default'  => 0,
                    'label-en_US' => 'Red',
                    'label-fr_FR' => 'Rouge'
                )
            ),
        );
    }

    /**
     * Add attribute option labels
     *
     * @param AttributeOption $option
     * @param array           $data
     */
    protected function addAttributeOptionLabels(AttributeOption $option, array $data)
    {
        foreach ($data as $key => $data) {
            if (strpos($key, 'label-') !== false) {
                $locale = str_replace('label-', '', $key);
                $value = new AttributeOptionValue();
                $value->setLocale($locale);
                $value->setValue($data);
                $option->addOptionValue($value);
            }
        }
    }
}
