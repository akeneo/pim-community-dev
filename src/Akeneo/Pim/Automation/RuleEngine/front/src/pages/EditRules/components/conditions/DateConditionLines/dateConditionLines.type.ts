import {Translate} from '../../../../../dependenciesTools';
import {Operator} from '../../../../../models/Operator';

type DateOperator =
  | Operator.IS_EMPTY
  | Operator.IS_NOT_EMPTY
  | Operator.BETWEEN
  | Operator.NOT_BETWEEN
  | Operator.LOWER_THAN
  | Operator.EQUALS
  | Operator.NOT_EQUAL
  | Operator.GREATER_THAN;

type DateBetweenValue = string[];
type DateStandardValue = string;
type DateValue = DateStandardValue | DateBetweenValue;

enum DateTypeOptionIds {
  SPECIFIC_DATE = 'SPECIFIC_DATE',
  PAST_DATE = 'PAST_DATE',
  FUTURE_DATE = 'FUTURE_DATE',
  PRESENT = 'PRESENT',
  NONE = 'NONE',
}

type DateTypeOption = {id: DateTypeOptionIds; text: string};

const createDateTypeOptions = (translate: Translate): DateTypeOption[] => [
  {
    id: DateTypeOptionIds.SPECIFIC_DATE,
    text: translate('pimee_catalog_rule.form.date.operators.specific_date'),
  },
  {
    id: DateTypeOptionIds.PAST_DATE,
    text: translate('pimee_catalog_rule.form.date.label.date_past'),
  },
  {
    id: DateTypeOptionIds.FUTURE_DATE,
    text: translate('pimee_catalog_rule.form.date.label.date_future'),
  },
  {
    id: DateTypeOptionIds.PRESENT,
    text: translate('pimee_catalog_rule.form.date.label.date_present'),
  },
];

type TimePeriodOption = {
  id: TimePeriodOptionsIds;
  getText: (nb: number) => string;
};

enum TimePeriodOptionsIds {
  DAY = 'DAY',
  WEEK = 'WEEK',
  MONTH = 'MONTH',
  YEAR = 'YEAR',
  HOUR = 'HOUR',
  MINUTE = 'MINUTE',
}

const createTimePeriodCalendarOptions = (
  translate: Translate
): TimePeriodOption[] => [
  {
    id: TimePeriodOptionsIds.DAY,
    getText: (nb: number) =>
      translate('pimee_catalog_rule.form.date.day', {}, nb),
  },
  {
    id: TimePeriodOptionsIds.WEEK,
    getText: (nb: number) =>
      translate('pimee_catalog_rule.form.date.week', {}, nb),
  },
  {
    id: TimePeriodOptionsIds.MONTH,
    getText: (nb: number) =>
      translate('pimee_catalog_rule.form.date.month', {}, nb),
  },
  {
    id: TimePeriodOptionsIds.YEAR,
    getText: (nb: number) =>
      translate('pimee_catalog_rule.form.date.year', {}, nb),
  },
];

const createTimePeriodClockOptions = (
  translate: Translate
): TimePeriodOption[] => [
  {
    id: TimePeriodOptionsIds.HOUR,
    getText: (nb: number) =>
      translate('pimee_catalog_rule.form.date.hour', {}, nb),
  },
  {
    id: TimePeriodOptionsIds.MINUTE,
    getText: (nb: number) =>
      translate('pimee_catalog_rule.form.date.minute', {}, nb),
  },
];

const isDateStandardValue = (value: DateValue): value is DateStandardValue =>
  typeof value === 'string';
const isDateBetweenValue = (value: DateValue): value is DateBetweenValue =>
  Array.isArray(value);

export {
  createDateTypeOptions,
  createTimePeriodCalendarOptions,
  createTimePeriodClockOptions,
  DateBetweenValue,
  DateOperator,
  DateStandardValue,
  DateTypeOption,
  DateTypeOptionIds,
  DateValue,
  isDateBetweenValue,
  isDateStandardValue,
  TimePeriodOption,
  TimePeriodOptionsIds,
};
