import {LabelCollection, LocaleCode} from '@akeneo-pim-community/shared';

type Attribute = {
  code: string;
  type: string;
  labels: LabelCollection;
  scopable: boolean;
  localizable: boolean;
  is_locale_specific: boolean;
  available_locales: LocaleCode[];
};

export type {Attribute};
