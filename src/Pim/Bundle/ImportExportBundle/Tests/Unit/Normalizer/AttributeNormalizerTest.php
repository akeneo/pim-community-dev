<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\AttributeNormalizer;
use Pim\Bundle\ImportExportBundle\Normalizer\TranslationNormalizer;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
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
class AttributeNormalizerTest extends NormalizerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new AttributeNormalizer(new TranslationNormalizer());
        $this->format     = 'json';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Entity\Attribute', 'json', true),
            array('Pim\Bundle\CatalogBundle\Entity\Attribute', 'xml', true),
            array('Pim\Bundle\CatalogBundle\Entity\Attribute', 'csv', false),
            array('stdClass', 'json', false),
            array('stdClass', 'xml', false),
            array('stdClass', 'csv', false),
        );
    }

    /**
     * {@inheritdoc}
     * @dataProvider getNormalizeData
     */
    public function testNormalize(array $data)
    {
        $attribute = $this->createEntity($data);

        $expectedResult = $data;
        foreach ($this->getOptionalProperties() as $property) {
            if (!array_key_exists($property, $expectedResult)) {
                $expectedResult[$property] = '';
            }
        }

        $this->assertEquals(
            $expectedResult,
            $this->normalizer->normalize($attribute, $this->format, array('versioning' => true))
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
                    'type'                   => 'pim_catalog_multiselect',
                    'code'                   => 'color',
                    'label'                  => array('en' => 'Color', 'fr' => 'Couleur'),
                    'group'                  => 'general',
                    'sort_order'             => '5',
                    'required'               => '0',
                    'unique'                 => '0',
                    'default_options'        => array('red' => array('en' => 'Red', 'fr' => 'Rouge')),
                    'default_value'          => array('red' => array('en' => 'Red', 'fr' => 'Rouge')),
                    'searchable'             => '1',
                    'localizable'            => '1',
                    'available_locales'      => array('en', 'fr'),
                    'date_type'              => '',
                    'metric_family'          => '',
                    'default_metric_unit'    => '',
                    'scope'                  => 'Global',
                    'options'                => array(
                        'green' => array('en' => 'Green', 'fr' => 'Vert'),
                        'red'   => array('en' => 'Red', 'fr' => 'Rouge')
                    ),
                    'useable_as_grid_column' => '1',
                    'useable_as_grid_filter' => '0',
                )
            ),
            array(
                array(
                    'type'                   => 'pim_catalog_text',
                    'code'                   => 'description',
                    'label'                  => array('en' => 'Description', 'fr' => 'Description'),
                    'group'                  => 'info',
                    'sort_order'             => '1',
                    'required'               => '1',
                    'unique'                 => '0',
                    'default_value'          => 'No description',
                    'default_options'        => array(),
                    'searchable'             => '1',
                    'localizable'            => '1',
                    'available_locales'      => array('en', 'fr'),
                    'date_type'              => '',
                    'metric_family'          => '',
                    'default_metric_unit'    => '',
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
     * @return array
     */
    protected function getOptionalProperties()
    {
        return array(
            'default_value',
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
            'allowed_extensions',
            'max_file_size',
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return Attribute
     */
    protected function createEntity(array $data)
    {
        $attribute = new Attribute();
        $attribute->setAttributeType($data['type']);

        $this->addLabels($attribute, $data);

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
        $attribute->setLocalizable($data['localizable']);
        $attribute->setScopable(strtolower($data['scope']) !== 'global');
        $attribute->setUseableAsGridColumn((bool) $data['useable_as_grid_column']);
        $attribute->setUseableAsGridFilter((bool) $data['useable_as_grid_filter']);
        $attribute->setDateType($data['date_type']);
        $attribute->setMetricFamily($data['metric_family']);
        $attribute->setDefaultMetricUnit($data['default_metric_unit']);

        $this->addAvailableLocales($attribute, $data);
        $this->addOptions($attribute, $data);
        $this->addDefaultOptions($attribute, $data);

        foreach ($this->getOptionalProperties() as $property) {
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

    /**
     * @param Attribute $attribute
     * @param array     $data
     */
    protected function addLabels($attribute, $data)
    {
        foreach ($data['label'] as $locale => $label) {
            $translation = $attribute->getTranslation($locale);
            $translation->setLabel($label);
        }
    }

    /**
     * @param Attribute $attribute
     * @param array     $data
     */
    protected function addAvailableLocales($attribute, $data)
    {
        foreach ($data['available_locales'] as $code) {
            $locale = new Locale();
            $locale->setCode($code);
            $attribute->addAvailableLocale($locale);
        }
    }

    /**
     * Create attribute options
     *
     * @param Attribute $attribute
     * @param array     $data
     */
    protected function addOptions(Attribute $attribute, $data)
    {
        if (count($data['options']) === 1) {
            $attribute->setBackendType('option');
        } elseif (count($data['options']) > 1) {
            $attribute->setBackendType('option');
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
    }

    /**
     * Add attribute default options
     *
     * @param Attribute $attribute
     * @param array     $data
     */
    protected function addDefaultOptions(Attribute $attribute, $data)
    {
        $defaultOptions = array_keys($data['default_options']);
        foreach ($defaultOptions as $code) {
            $options = $attribute->getOptions();
            foreach ($options as $option) {
                if ($code == $option->getCode()) {
                    $option->setDefault(true);
                }
            }
        }
    }
}
