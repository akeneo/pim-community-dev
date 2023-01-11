import {CONDITION_NAMES} from './conditions';
import {Operator} from './operator';
import {OptionCode} from '../option';
import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';

type SimpleSelectCondition = {
  type: CONDITION_NAMES.SELECT_OPTION;
  operator: Operator.IN | Operator.NOT_IN | Operator.EMPTY | Operator.NOT_EMPTY;
  value?: OptionCode[];
  attributeCode: string;
  locale?: LocaleCode | null;
  scope?: ChannelCode | null;
};

export type {SimpleSelectCondition};
