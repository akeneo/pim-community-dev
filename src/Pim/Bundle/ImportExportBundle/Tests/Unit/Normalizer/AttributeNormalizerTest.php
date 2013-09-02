<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\AttributeNormalizer;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\Entity\Locale;

/**
 * Test class for AttributeNormalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNormalizerTest extends \PHPUnit_Framework_TestCase
{
    private $normalizer;

    private $optionalProperties = array(
        'description',
        'default_value',
        'value_creation_allowed',
        'max_characters',
        'validation_rule',
        'validation_regexp',
        'wysiwyg_enabled',
        'number_min',
        'number_max',
        'decimals_allowed',
        'negative_allowed',
        'date_min',
        'date_max',
        'date_type',
        'metric_family',
        'default_metric_unit',
        'allowed_file_sources',
        'allowed_extensions',
        'max_file_size',
    );

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new AttributeNormalizer();
    }

    /**
     * Data provider for testing supportsNormalization method
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\ProductAttribute', 'json',  true),
            array('Pim\Bundle\CatalogBundle\Entity\ProductAttribute', 'csv', false),
            array('stdClass',                                         'json',  false),
            array('stdClass',                                         'csv', false),
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
                    'type'                   => 'multiselect',
                    'code'                   => 'color',
                    'label'                  => array('en' => 'Color', 'fr' => 'Couleur'),
                    'description'            => 'Color of the product',
                    'group'                  => 'general',
                    'sort_order'             => '5',
                    'required'               => '0',
                    'unique'                 => '0',
                    'default_options'        => array('red' => array('en' => 'Red', 'fr' => 'Rouge')),
                    'searchable'             => '1',
                    'localizable'            => '1',
                    'available_locales'      => array('en', 'fr'),
                    'scope'                  => 'Global',
                    'options'                => array(
                        'green' => array('en' => 'Green', 'fr' => 'Vert'),
                        'red'   => array('en' => 'Red', 'fr' => 'Rouge')
                    ),
                    'useable_as_grid_column' => '1',
                    'useable_as_grid_filter' => '0',
                    'value_creation_allowed' => '1',
                )
            ),
            array(
                array(
                    'type'                   => 'text',
                    'code'                   => 'description',
                    'label'                  => array('en' => 'Description', 'fr' => 'Description'),
                    'description'            => 'A desccription of the product',
                    'group'                  => 'info',
                    'sort_order'             => '1',
                    'required'               => '1',
                    'unique'                 => '0',
                    'default_value'          => 'No description',
                    'default_options'        => array(),
                    'searchable'             => '1',
                    'localizable'            => '1',
                    'available_locales'      => array('en', 'fr'),
                    'scope'                  => 'Channel',
                    'options'                => array(),
                    'useable_as_grid_column' => '1',
                    'useable_as_grid_filter' => '1',
                    'max_characters'         => '200',
                    'validation_rule'        => 'regexp',
                    'validation_regexp'      => '^[a-zA-Z0-9 ]*$',
                    'wysiwyg_enabled'        => '1',
                )
            )
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
        $attribute = $this->createAttribute($data);

        $expectedResult = $data;
        foreach ($this->optionalProperties as $property) {
            if (!array_key_exists($property, $expectedResult)) {
                $expectedResult[$property] = '';
            }
        }

        $normalized = $this->normalizer->normalize($attribute, 'json');
        $this->assertEquals($expectedResult, $normalized);
    }

    /**
     * Create a attribute
     * @param array $data
     *
     * @return attribute
     */
    private function createAttribute(array $data)
    {
        $attribute = new ProductAttribute();
        $attribute->setAttributeType(sprintf('pim_catalog_%s', strtolower($data['type'])));

        foreach ($data['label'] as $locale => $label) {
            $translation = $attribute->getTranslation($locale);
            $translation->setLabel($label);
        }

        if ($data['group'] !== '') {
            $group = new AttributeGroup();
            $group->setCode($data['group']);
            $attribute->setGroup($group);
        }

        $attribute->setCode($data['code']);
        $attribute->setSortOrder($data['sort_order']);
        $attribute->setRequired($data['required']);
        $attribute->setUnique($data['unique']);
        $attribute->setSearchable($data['searchable']);
        $attribute->setTranslatable($data['localizable']);
        $attribute->setScopable(strtolower($data['scope']) !== 'global');
        $attribute->setUseableAsGridColumn((bool) $data['useable_as_grid_column']);
        $attribute->setUseableAsGridFilter((bool) $data['useable_as_grid_filter']);

        foreach ($data['available_locales'] as $code) {
            $locale = new Locale();
            $locale->setCode($code);
            $attribute->addAvailableLocale($locale);
        }

        foreach ($data['options'] as $code => $values) {
            $attributeOption = new AttributeOption();
            $attributeOption->setCode($code);
            foreach ($values as $locale => $value) {
                $optionValue = new AttributeOptionValue();
                $optionValue->setLocale($locale);
                $optionValue->setValue($value);
                $attributeOption->addOptionValue($optionValue);
            }
            $attribute->addOption($attributeOption);
        }

        foreach ($data['default_options'] as $code => $values) {
            foreach ($attribute->getOptions() as $option) {
                if ($code == $option->getCode()) {
                    $option->setDefault(true);
                }
            }
        }

        foreach ($this->optionalProperties as $property) {
            if (isset($data[$property]) && $data[$property] !== '') {
                $method = 'set' . implode(
                    '',
                    array_map(
                        function ($item) {
                            return ucfirst($item);
                        },
                        explode('_', $property)
                    )
                );
                $attribute->$method($data[$property]);
            }
        }

        return $attribute;
    }
}
