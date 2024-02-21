export enum ATTRIBUTE_TYPE {
  BOOLEAN = 'pim_catalog_boolean',
  DATE = 'pim_catalog_date',
  FILE = 'pim_catalog_file',
  IDENTIFIER = 'pim_catalog_identifier',
  IMAGE = 'pim_catalog_image',
  METRIC = 'pim_catalog_metric',
  NUMBER = 'pim_catalog_number',
  OPTION_MULTI_SELECT = 'pim_catalog_multiselect',
  OPTION_SIMPLE_SELECT = 'pim_catalog_simpleselect',
  PRICE_COLLECTION = 'pim_catalog_price_collection',
  TEXTAREA = 'pim_catalog_textarea',
  TEXT = 'pim_catalog_text',
  REFERENCE_DATA_MULTI_SELECT = 'pim_reference_data_multiselect',
  REFERENCE_DATA_SIMPLE_SELECT = 'pim_reference_data_simpleselect',
  REFERENCE_ENTITY_SIMPLE_SELECT = 'akeneo_reference_entity',
  REFERENCE_ENTITY_COLLECTION = 'akeneo_reference_entity_collection',
  ASSET_COLLECTION = 'pim_catalog_asset_collection',
  LEGACY_ASSET_COLLECTION = 'pim_assets_collection',
  TABLE = 'pim_catalog_table',
}

export type AttributeType =
  | ATTRIBUTE_TYPE.BOOLEAN
  | ATTRIBUTE_TYPE.DATE
  | ATTRIBUTE_TYPE.FILE
  | ATTRIBUTE_TYPE.IDENTIFIER
  | ATTRIBUTE_TYPE.IMAGE
  | ATTRIBUTE_TYPE.METRIC
  | ATTRIBUTE_TYPE.NUMBER
  | ATTRIBUTE_TYPE.OPTION_MULTI_SELECT
  | ATTRIBUTE_TYPE.OPTION_SIMPLE_SELECT
  | ATTRIBUTE_TYPE.PRICE_COLLECTION
  | ATTRIBUTE_TYPE.TEXTAREA
  | ATTRIBUTE_TYPE.TEXT
  | ATTRIBUTE_TYPE.REFERENCE_DATA_MULTI_SELECT
  | ATTRIBUTE_TYPE.REFERENCE_DATA_SIMPLE_SELECT
  | ATTRIBUTE_TYPE.REFERENCE_ENTITY_SIMPLE_SELECT
  | ATTRIBUTE_TYPE.REFERENCE_ENTITY_COLLECTION
  | ATTRIBUTE_TYPE.ASSET_COLLECTION
  | ATTRIBUTE_TYPE.LEGACY_ASSET_COLLECTION
  | ATTRIBUTE_TYPE.TABLE;
