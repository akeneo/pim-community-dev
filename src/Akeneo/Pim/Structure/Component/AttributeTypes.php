<?php

namespace Akeneo\Pim\Structure\Component;

/**
 * Attribute types dictionary
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeTypes
{
    public const BOOLEAN = 'pim_catalog_boolean';
    public const DATE = 'pim_catalog_date';
    public const FILE = 'pim_catalog_file';
    public const IDENTIFIER = 'pim_catalog_identifier';
    public const IMAGE = 'pim_catalog_image';
    public const METRIC = 'pim_catalog_metric';
    public const NUMBER = 'pim_catalog_number';
    public const OPTION_MULTI_SELECT = 'pim_catalog_multiselect';
    public const OPTION_SIMPLE_SELECT = 'pim_catalog_simpleselect';
    public const PRICE_COLLECTION = 'pim_catalog_price_collection';
    public const TEXTAREA = 'pim_catalog_textarea';
    public const TEXT = 'pim_catalog_text';
    public const REFERENCE_DATA_MULTI_SELECT = 'pim_reference_data_multiselect';
    public const REFERENCE_DATA_SIMPLE_SELECT = 'pim_reference_data_simpleselect';
    public const REFERENCE_ENTITY_SIMPLE_SELECT = 'akeneo_reference_entity';
    public const REFERENCE_ENTITY_COLLECTION = 'akeneo_reference_entity_collection';
    public const ASSET_COLLECTION = 'pim_catalog_asset_collection';
    public const LEGACY_ASSET_COLLECTION = 'pim_assets_collection';

    public const BACKEND_TYPE_BOOLEAN = 'boolean';
    public const BACKEND_TYPE_COLLECTION = 'collections';
    public const BACKEND_TYPE_DATE = 'date';
    public const BACKEND_TYPE_DATETIME = 'datetime';
    public const BACKEND_TYPE_DECIMAL = 'decimal';
    public const BACKEND_TYPE_ENTITY = 'entity';
    public const BACKEND_TYPE_INTEGER = 'integer';
    public const BACKEND_TYPE_MEDIA = 'media';
    public const BACKEND_TYPE_METRIC = 'metric';
    public const BACKEND_TYPE_OPTION = 'option';
    public const BACKEND_TYPE_OPTIONS = 'options';
    public const BACKEND_TYPE_PRICE = 'prices';
    public const BACKEND_TYPE_REF_DATA_OPTION = 'reference_data_option';
    public const BACKEND_TYPE_REF_DATA_OPTIONS = 'reference_data_options';
    public const BACKEND_TYPE_TEXTAREA = 'textarea';
    public const BACKEND_TYPE_TEXT = 'text';
}
