<?php

namespace Akeneo\Tool\Component\Api\tests\integration\Normalizer;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @group ce
 */
class AttributeIntegration extends AbstractNormalizerTestCase
{
    public function testAttributeIdentifier()
    {
        $expected = [
            'code'                   => 'sku',
            'type'                   => 'pim_catalog_identifier',
            'group'                  => 'attributeGroupA',
            'group_labels'           => ['en_US' => 'Attribute group A', 'fr_FR' => 'Groupe d\'attribut A'],
            'unique'                 => true,
            'useable_as_grid_filter' => true,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => ['en_US' => 'this is the sku'],
            'auto_option_sorting'    => null,
            'default_value'          => null,
            'is_main_identifier'     => true,
        ];

        $this->assert('sku', $expected);
    }

    public function testAttributeDate()
    {
        $expected = [
            'code'                   => 'a_date',
            'type'                   => 'pim_catalog_date',
            'group'                  => 'attributeGroupA',
            'group_labels'           => ['en_US' => 'Attribute group A', 'fr_FR' => 'Groupe d\'attribut A'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => '2005-05-25T00:00:00+02:00',
            'date_max'               => '2050-12-31T00:00:00+01:00',
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 2,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => ['en_US' => 'date guidelines'],
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_date', $expected);
    }

    public function testAttributeFile()
    {
        $expected = [
            'code'                   => 'a_file',
            'type'                   => 'pim_catalog_file',
            'group'                  => 'attributeGroupA',
            'group_labels'           => ['en_US' => 'Attribute group A', 'fr_FR' => 'Groupe d\'attribut A'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => ['pdf', 'doc', 'docx', 'txt'],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 1,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_file', $expected);
    }

    public function testAttributeImage()
    {
        $expected = [
            'code'                   => 'an_image',
            'type'                   => 'pim_catalog_image',
            'group'                  => 'attributeGroupA',
            'group_labels'           => ['en_US' => 'Attribute group A', 'fr_FR' => 'Groupe d\'attribut A'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => ['jpg', 'gif', 'png'],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => '500.00',
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('an_image', $expected);
    }

    public function testAttributeMetric()
    {
        $expected = [
            'code'                   => 'a_metric',
            'type'                   => 'pim_catalog_metric',
            'group'                  => 'attributeGroupB',
            'group_labels'           => ['en_US' => 'Attribute group B', 'fr_FR' => 'Groupe d\'attribut B'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => 'Power',
            'default_metric_unit'    => 'KILOWATT',
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => true,
            'negative_allowed'       => false,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_metric', $expected);
    }

    public function testAttributeMetricWithoutDecimal()
    {
        $expected = [
            'code'                   => 'a_metric_without_decimal',
            'type'                   => 'pim_catalog_metric',
            'group'                  => 'attributeGroupB',
            'group_labels'           => ['en_US' => 'Attribute group B', 'fr_FR' => 'Groupe d\'attribut B'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => 'Length',
            'default_metric_unit'    => 'METER',
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => false,
            'negative_allowed'       => false,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_metric_without_decimal', $expected);
    }

    public function testAttributeMetricWithoutDecimalNegative()
    {
        $expected = [
            'code'                   => 'a_metric_without_decimal_negative',
            'type'                   => 'pim_catalog_metric',
            'group'                  => 'attributeGroupC',
            'group_labels'           => ['en_US' => 'Attribute group C', 'fr_FR' => 'Groupe d\'attribut C'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => 'Temperature',
            'default_metric_unit'    => 'CELSIUS',
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => false,
            'negative_allowed'       => true,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_metric_without_decimal_negative', $expected);
    }

    public function testAttributeMetricNegative()
    {
        $expected = [
            'code'                   => 'a_metric_negative',
            'type'                   => 'pim_catalog_metric',
            'group'                  => 'attributeGroupB',
            'group_labels'           => ['en_US' => 'Attribute group B', 'fr_FR' => 'Groupe d\'attribut B'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => 'Temperature',
            'default_metric_unit'    => 'CELSIUS',
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => true,
            'negative_allowed'       => true,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_metric_negative', $expected);
    }

    public function testAttributeMultiSelect()
    {
        $expected = [
            'code'                   => 'a_multi_select',
            'type'                   => 'pim_catalog_multiselect',
            'group'                  => 'attributeGroupC',
            'group_labels'           => ['en_US' => 'Attribute group C', 'fr_FR' => 'Groupe d\'attribut C'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => false,
            'default_value'          => null,
        ];

        $this->assert('a_multi_select', $expected);
    }

    public function testAttributeNumberFloat()
    {
        $expected = [
            'code'                   => 'a_number_float',
            'type'                   => 'pim_catalog_number',
            'group'                  => 'attributeGroupB',
            'group_labels'           => ['en_US' => 'Attribute group B', 'fr_FR' => 'Groupe d\'attribut B'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => true,
            'negative_allowed'       => false,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_number_float', $expected);
    }

    public function testAttributeNumberFloatNegative()
    {
        $expected = [
            'code'                   => 'a_number_float_negative',
            'type'                   => 'pim_catalog_number',
            'group'                  => 'attributeGroupB',
            'group_labels'           => ['en_US' => 'Attribute group B', 'fr_FR' => 'Groupe d\'attribut B'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => '-250.0000',
            'number_max'             => '1000.0000',
            'decimals_allowed'       => true,
            'negative_allowed'       => true,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_number_float_negative', $expected);
    }

    public function testAttributeNumberInteger()
    {
        $expected = [
            'code'                   => 'a_number_integer',
            'type'                   => 'pim_catalog_number',
            'group'                  => 'attributeGroupB',
            'group_labels'           => ['en_US' => 'Attribute group B', 'fr_FR' => 'Groupe d\'attribut B'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => false,
            'negative_allowed'       => false,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_number_integer', $expected);
    }

    public function testAttributeNumberIntegerNegative()
    {
        $expected = [
            'code'                   => 'a_number_integer_negative',
            'type'                   => 'pim_catalog_number',
            'group'                  => 'attributeGroupB',
            'group_labels'           => ['en_US' => 'Attribute group B', 'fr_FR' => 'Groupe d\'attribut B'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => false,
            'negative_allowed'       => true,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_number_integer_negative', $expected);
    }

    public function testAttributePrice()
    {
        $expected = [
            'code'                   => 'a_price',
            'type'                   => 'pim_catalog_price_collection',
            'group'                  => 'attributeGroupA',
            'group_labels'           => ['en_US' => 'Attribute group A', 'fr_FR' => 'Groupe d\'attribut A'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => true,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 3,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_price', $expected);
    }

    public function testAttributePriceWithoutDecimal()
    {
        $expected = [
            'code'                   => 'a_price_without_decimal',
            'type'                   => 'pim_catalog_price_collection',
            'group'                  => 'attributeGroupA',
            'group_labels'           => ['en_US' => 'Attribute group A', 'fr_FR' => 'Groupe d\'attribut A'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => false,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 11,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_price_without_decimal', $expected);
    }

    public function testAttributeReferenceDataMultiSelect()
    {
        $expected = [
            'code'                   => 'a_ref_data_multi_select',
            'type'                   => 'pim_reference_data_multiselect',
            'group'                  => 'attributeGroupA',
            'group_labels'           => ['en_US' => 'Attribute group A', 'fr_FR' => 'Groupe d\'attribut A'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => 'fabrics',
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 4,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_ref_data_multi_select', $expected);
    }

    public function testAttributeReferenceDataSimpleSelect()
    {
        $expected = [
            'code'                   => 'a_ref_data_simple_select',
            'type'                   => 'pim_reference_data_simpleselect',
            'group'                  => 'attributeGroupA',
            'group_labels'           => ['en_US' => 'Attribute group A', 'fr_FR' => 'Groupe d\'attribut A'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => 'color',
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 5,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_ref_data_simple_select', $expected);
    }

    public function testAttributeSimpleSelect()
    {
        $expected = [
            'code'                   => 'a_simple_select',
            'type'                   => 'pim_catalog_simpleselect',
            'group'                  => 'attributeGroupB',
            'group_labels'           => ['en_US' => 'Attribute group B', 'fr_FR' => 'Groupe d\'attribut B'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => true,
            'default_value'          => null,
        ];

        $this->assert('a_simple_select', $expected);
    }

    public function testAttributeText()
    {
        $expected = [
            'code'                   => 'a_text',
            'type'                   => 'pim_catalog_text',
            'group'                  => 'attributeGroupA',
            'group_labels'           => ['en_US' => 'Attribute group A', 'fr_FR' => 'Groupe d\'attribut A'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => 200,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 6,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_text', $expected);
    }

    public function testAttributeTextArea()
    {
        $expected = [
            'code'                   => 'a_text_area',
            'type'                   => 'pim_catalog_textarea',
            'group'                  => 'attributeGroupA',
            'group_labels'           => ['en_US' => 'Attribute group A', 'fr_FR' => 'Groupe d\'attribut A'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => true,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 7,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_text_area', $expected);
    }

    public function testAttributeBoolean()
    {
        $expected = [
            'code'                   => 'a_yes_no',
            'type'                   => 'pim_catalog_boolean',
            'group'                  => 'attributeGroupA',
            'group_labels'           => ['en_US' => 'Attribute group A', 'fr_FR' => 'Groupe d\'attribut A'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 8,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_yes_no', $expected);
    }

    public function testBooleanAttributeDefaultValue()
    {
        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_yes_no');
        $this->get('pim_catalog.updater.attribute')->update($attribute, ['default_value' => true]);
        Assert::assertCount(0, $this->get('validator')->validate($attribute));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $expected = [
            'code' => 'a_yes_no',
            'type' => 'pim_catalog_boolean',
            'group' => 'attributeGroupA',
            'group_labels' => ['en_US' => 'Attribute group A', 'fr_FR' => 'Groupe d\'attribut A'],
            'unique' => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions' => [],
            'metric_family' => null,
            'default_metric_unit' => null,
            'reference_data_name' => null,
            'available_locales' => [],
            'max_characters' => null,
            'validation_rule' => null,
            'validation_regexp' => null,
            'wysiwyg_enabled' => null,
            'number_min' => null,
            'number_max' => null,
            'decimals_allowed' => null,
            'negative_allowed' => null,
            'date_min' => null,
            'date_max' => null,
            'max_file_size' => null,
            'minimum_input_length' => null,
            'sort_order' => 8,
            'localizable' => false,
            'scopable' => false,
            'labels' => new \StdClass(),
            'guidelines' => new \stdClass(),
            'auto_option_sorting' => null,
            'default_value' => true,
        ];

        $this->assert('a_yes_no', $expected);
    }

    public function testAttributeLocalizableImage()
    {
        $expected = [
            'code'                   => 'a_localizable_image',
            'type'                   => 'pim_catalog_image',
            'group'                  => 'attributeGroupB',
            'group_labels'           => ['en_US' => 'Attribute group B', 'fr_FR' => 'Groupe d\'attribut B'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => true,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_localizable_image', $expected);
    }

    public function testAttributeScopablePrice()
    {
        $expected = [
            'code'                   => 'a_scopable_price',
            'type'                   => 'pim_catalog_price_collection',
            'group'                  => 'attributeGroupA',
            'group_labels'           => ['en_US' => 'Attribute group A', 'fr_FR' => 'Groupe d\'attribut A'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => true,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 9,
            'localizable'            => false,
            'scopable'               => true,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_scopable_price', $expected);
    }

    public function testAttributeLocalizableAndScopableTextArea()
    {
        $expected = [
            'code'                   => 'a_localized_and_scopable_text_area',
            'type'                   => 'pim_catalog_textarea',
            'group'                  => 'attributeGroupA',
            'group_labels'           => ['en_US' => 'Attribute group A', 'fr_FR' => 'Groupe d\'attribut A'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => false,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 10,
            'localizable'            => true,
            'scopable'               => true,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_localized_and_scopable_text_area', $expected);
    }

    public function testAttributeRegexp()
    {
        $expected = [
            'code'                   => 'a_regexp',
            'type'                   => 'pim_catalog_text',
            'group'                  => 'attributeGroupA',
            'group_labels'           => ['en_US' => 'Attribute group A', 'fr_FR' => 'Groupe d\'attribut A'],
            'unique'                 => false,
            'useable_as_grid_filter' => false,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => ['en_US'],
            'max_characters'         => null,
            'validation_rule'        => 'regexp',
            'validation_regexp'      => '([0-9]+)',
            'wysiwyg_enabled'        => null,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => null,
            'negative_allowed'       => null,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => null,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => new \StdClass(),
            'guidelines'             => new \StdClass(),
            'auto_option_sorting'    => null,
            'default_value'          => null,
        ];

        $this->assert('a_regexp', $expected);
    }

    private function assert(string $identifier, array $expected): void
    {
        $attribute = $this->getAttributeRepository()->findOneByIdentifier($identifier);
        $result = $this->getApiSerializer()->normalize($attribute, 'external_api');

        $this->assertEquals($expected, $result);
    }

    private function getAttributeRepository(): AttributeRepositoryInterface
    {
        return $this->get('pim_catalog.repository.attribute');
    }

    private function getApiSerializer(): SerializerInterface
    {
        return $this->get('pim_external_api_serializer');
    }
}

