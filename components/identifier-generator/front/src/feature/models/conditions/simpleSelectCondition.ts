import {CONDITION_NAMES} from './conditions';
import {Operator} from './operator';
import {OptionCode} from '../option';
import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';

type SimpleSelectCondition = {
  type: CONDITION_NAMES.SIMPLE_SELECT;
  attributeCode: string;
  locale?: LocaleCode | null;
  scope?: ChannelCode | null;
  value?: OptionCode[];
} & (
  | {
      type: CONDITION_NAMES.SIMPLE_SELECT;
      attributeCode: string;
      locale?: LocaleCode | null;
      scope?: ChannelCode | null;
      operator: Operator.IN | Operator.NOT_IN;
      value: OptionCode[];
    }
  | {
      type: CONDITION_NAMES.SIMPLE_SELECT;
      attributeCode: string;
      locale?: LocaleCode | null;
      scope?: ChannelCode | null;
      operator: Operator.EMPTY | Operator.NOT_EMPTY;
    }
);

export type {SimpleSelectCondition};
