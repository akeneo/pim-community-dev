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
    const BOOLEAN = 'pim_catalog_boolean';
    const DATE = 'pim_catalog_date';
    const FILE = 'pim_catalog_file';
    const IDENTIFIER = 'pim_catalog_identifier';
    const IMAGE = 'pim_catalog_image';
    const METRIC = 'pim_catalog_metric';
    const NUMBER = 'pim_catalog_number';
    const OPTION_MULTI_SELECT = 'pim_catalog_multiselect';
    const OPTION_SIMPLE_SELECT = 'pim_catalog_simpleselect';
    const PRICE_COLLECTION = 'pim_catalog_price_collection';
    const TEXTAREA = 'pim_catalog_textarea';
    const TEXT = 'pim_catalog_text';
    const REFERENCE_DATA_MULTI_SELECT = 'pim_reference_data_multiselect';
    const REFERENCE_DATA_SIMPLE_SELECT = 'pim_reference_data_simpleselect';
    const REFERENCE_ENTITY_SIMPLE_SELECT = 'akeneo_reference_entity';
    const REFERENCE_ENTITY_COLLECTION = 'akeneo_reference_entity_collection';
    const ASSET_COLLECTION = 'pim_catalog_asset_collection';
    const LEGACY_ASSET_COLLECTION = 'pim_assets_collection';

    const BACKEND_TYPE_BOOLEAN = 'boolean';
    const BACKEND_TYPE_COLLECTION = 'collections';
    const BACKEND_TYPE_DATE = 'date';
    const BACKEND_TYPE_DATETIME = 'datetime';
    const BACKEND_TYPE_DECIMAL = 'decimal';
    const BACKEND_TYPE_ENTITY = 'entity';
    const BACKEND_TYPE_INTEGER = 'integer';
    const BACKEND_TYPE_MEDIA = 'media';
    const BACKEND_TYPE_METRIC = 'metric';
    const BACKEND_TYPE_OPTION = 'option';
    const BACKEND_TYPE_OPTIONS = 'options';
    const BACKEND_TYPE_PRICE = 'prices';
    const BACKEND_TYPE_REF_DATA_OPTION = 'reference_data_option';
    const BACKEND_TYPE_REF_DATA_OPTIONS = 'reference_data_options';
    const BACKEND_TYPE_TEXTAREA = 'textarea';
    const BACKEND_TYPE_TEXT = 'text';
}
