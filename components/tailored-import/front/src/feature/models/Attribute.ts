import {LabelCollection, LocaleCode} from '@akeneo-pim-community/shared';

type Attribute = {
  code: string;
  type: string;
  labels: LabelCollection;
  scopable: boolean;
  localizable: boolean;
  is_locale_specific: boolean;
  available_locales: LocaleCode[];
  metric_family?: string;
  default_metric_unit?: string;
  reference_data_name?: string;
  decimals_allowed?: boolean;
};

const isMultiSourceAttribute = ({type}: Attribute): boolean =>
  ['pim_catalog_multiselect', 'akeneo_reference_entity_collection', 'pim_catalog_asset_collection'].includes(type);

export type {Attribute};
export {isMultiSourceAttribute};
