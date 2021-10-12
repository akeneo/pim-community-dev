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
  reference_data_name?: string;
};

export type {Attribute};
