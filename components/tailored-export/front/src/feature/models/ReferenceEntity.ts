import {LabelCollection} from '@akeneo-pim-community/shared';

type ReferenceEntityAttribute = {
  identifier: string;
  code: string;
  labels: LabelCollection;
  type: string;
  value_per_channel: boolean;
  value_per_locale: boolean;
};

type Record = {
  code: string;
  labels: LabelCollection;
};

export type {Record, ReferenceEntityAttribute};
