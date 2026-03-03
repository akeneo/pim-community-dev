import {PROPERTY_NAMES} from '../structure';
import {AttributeCode} from '../attribute';
import {AbbreviationType} from './abbreviationType';
import {Operator} from '../conditions/operator';
import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';

type RefEntityProperty = {
  type: PROPERTY_NAMES.REF_ENTITY;
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

export type {RefEntityProperty};
