import {PROPERTY_NAMES} from '../structure';
import {AbbreviationType} from './abbreviationType';
import {Operator} from '../conditions/operator';
import {AttributeCode} from '../attribute';
import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';

type SimpleSelectProperty = {
  type: PROPERTY_NAMES.SIMPLE_SELECT;
  attributeCode?: AttributeCode;
  locale?: LocaleCode | null;
  scope?: ChannelCode | null;
  process:
    | {
        type: AbbreviationType.NO;
      }
    | {
        type: AbbreviationType.TRUNCATE;
        operator: Operator | null;
        value: number | null;
      }
    | {
        type: AbbreviationType.NOMENCLATURE;
      }
    | {type: null};
};

export type {SimpleSelectProperty};
