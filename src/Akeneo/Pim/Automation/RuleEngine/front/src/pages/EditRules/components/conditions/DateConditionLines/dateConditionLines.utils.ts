import moment from 'moment';
import isEmpty from 'lodash/isEmpty';
import { Operator } from '../../../../../models/Operator';
import {
  DateTypeOptionIds,
  DateValue,
  DateStandardValue,
  TimePeriodOptionsIds,
  isDateBetweenValue,
} from './dateConditionLines.type';
import { dateAttributeOperators } from '../../../../../models/conditions';
import { Translate } from '../../../../../dependenciesTools';

const PRESENT_DEFAULT_VALUE = 'now';
const PAST_DATE_DEFAULT_VALUE = '-0 day';
const FUTURE_DATE_DEFAULT_VALUE = '+0 day';

const defaultDateTypeOperatorsValue: {
  [key in DateTypeOptionIds]: DateStandardValue;
} = {
  [DateTypeOptionIds.FUTURE_DATE]: FUTURE_DATE_DEFAULT_VALUE,
  [DateTypeOptionIds.NONE]: '',
  [DateTypeOptionIds.PAST_DATE]: PAST_DATE_DEFAULT_VALUE,
  [DateTypeOptionIds.PRESENT]: PRESENT_DEFAULT_VALUE,
  [DateTypeOptionIds.SPECIFIC_DATE]: '',
};

const relativeDateAttributeOperators = [
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.LOWER_THAN,
  Operator.GREATER_THAN,
];

const isRelativeDateOperatorSelected = (dateType: DateTypeOptionIds) =>
  dateType === DateTypeOptionIds.PRESENT ||
  dateType === DateTypeOptionIds.FUTURE_DATE ||
  dateType === DateTypeOptionIds.PAST_DATE;

const getDateAttributeOperatorsFromDateType = (dateType: DateTypeOptionIds) => {
  if (isRelativeDateOperatorSelected(dateType)) {
    return relativeDateAttributeOperators;
  }
  return dateAttributeOperators;
};

const formatPastFutureDate = (
  timePeriod: TimePeriodOptionsIds,
  timeValue: string,
  dateType: DateTypeOptionIds
) => {
  const sign = dateType === DateTypeOptionIds.PAST_DATE ? '-' : '+';
  return `${sign}${timeValue} ${timePeriod.toLocaleLowerCase()}`;
};

const getDateTypeFromValue = (value: DateValue) => {
  if (typeof value === 'undefined' || Array.isArray(value)) {
    return DateTypeOptionIds.NONE;
  }
  if (value === PRESENT_DEFAULT_VALUE) {
    return DateTypeOptionIds.PRESENT;
  }
  const relativeDateSign = value.charAt(0);
  if (relativeDateSign === '+') {
    return DateTypeOptionIds.FUTURE_DATE;
  } else if (relativeDateSign === '-') {
    return DateTypeOptionIds.PAST_DATE;
  }
  return DateTypeOptionIds.SPECIFIC_DATE;
};

const getTimeValueFromValue = (value: DateStandardValue) => {
  if (!value) {
    return '0';
  }
  const valueWithoutDateType = value.slice(1, value.length);
  return valueWithoutDateType.split(' ')[0];
};

const getTimePeriodFromValue = (value: DateStandardValue) => {
  if (!value) {
    return TimePeriodOptionsIds.DAY;
  }
  const valueWithoutDateType = value.slice(1, value.length);
  return valueWithoutDateType.split(' ')[1].toUpperCase();
};

const shouldRenderPresentValue = (
  dateType: DateTypeOptionIds,
  value: DateStandardValue
) => dateType === DateTypeOptionIds.PRESENT && value === PRESENT_DEFAULT_VALUE;

const shouldRenderSpecificDateValue = (
  dateType: DateTypeOptionIds,
  value: DateStandardValue
) =>
  dateType === DateTypeOptionIds.SPECIFIC_DATE &&
  (value || isEmpty(value) || !isNaN(new Date(value).getTime()));

const shouldRenderRelativeDateValue = (
  dateType: DateTypeOptionIds,
  value: DateStandardValue
) =>
  isRelativeDateOperatorSelected(dateType) &&
  !value.includes(PRESENT_DEFAULT_VALUE);

const isARangeOperator = (operator: Operator) =>
  operator === Operator.BETWEEN || operator === Operator.NOT_BETWEEN;

const isNotARangeOperator = (operator: Operator) =>
  operator !== Operator.BETWEEN && operator !== Operator.NOT_BETWEEN;

const isAnEmptyOperator = (operator: Operator) =>
  operator === Operator.IS_EMPTY || operator === Operator.IS_NOT_EMPTY;

const isNotAnEmptyOperator = (operator: Operator) =>
  operator !== Operator.IS_EMPTY && operator !== Operator.IS_NOT_EMPTY;

const shouldRenderRangeValue = (
  operator: Operator,
  dateType: DateTypeOptionIds
) => isARangeOperator(operator) && dateType === DateTypeOptionIds.NONE;

const validateDateConditionLine = (
  value: DateValue,
  dateType: DateTypeOptionIds,
  operator: Operator,
  translate: Translate
) => {
  if (dateType === DateTypeOptionIds.SPECIFIC_DATE && isEmpty(value)) {
    return translate('pimee_catalog_rule.exceptions.required');
  } else if (isARangeOperator(operator)) {
    if (isDateBetweenValue(value)) {
      if (isEmpty(value[0]) || isEmpty(value[1])) {
        return translate('pimee_catalog_rule.exceptions.required');
      }
      if (new Date(value[0]).getTime() > new Date(value[1]).getTime()) {
        return translate('pimee_catalog_rule.exceptions.wrong_date_timespan');
      }
    }
  }
  return true;
};

const formatDateLocaleTimeConditions = (pattern: string) => (
  conditions: any[]
) => {
  return conditions.map(condition => {
    if (
      condition &&
      (condition.field === 'created' || condition.field === 'updated')
    ) {
      const dateValue = new Date(condition.value);
      if (!isNaN(dateValue.getTime())) {
        return {
          ...condition,
          value: moment(dateValue).format(pattern),
        };
      }
      if (Array.isArray(condition.value)) {
        return {
          ...condition,
          value: [
            moment(new Date(condition.value[0])).format(pattern),
            moment(new Date(condition.value[1])).format(pattern),
          ],
        };
      }
      return {
        ...condition,
        value: condition.value,
      };
    }
    return condition;
  }, []);
};

const formatDateLocaleTimeConditionsFromBackend = formatDateLocaleTimeConditions(
  'YYYY-MM-DDTHH:mm'
);
const formatDateLocaleTimeConditionsToBackend = formatDateLocaleTimeConditions(
  'YYYY-MM-DD HH:mm:ss'
);

export {
  defaultDateTypeOperatorsValue,
  formatDateLocaleTimeConditionsFromBackend,
  formatDateLocaleTimeConditionsToBackend,
  formatPastFutureDate,
  FUTURE_DATE_DEFAULT_VALUE,
  getDateAttributeOperatorsFromDateType,
  getDateTypeFromValue,
  getTimePeriodFromValue,
  getTimeValueFromValue,
  isAnEmptyOperator,
  isARangeOperator,
  isNotAnEmptyOperator,
  isNotARangeOperator,
  isRelativeDateOperatorSelected,
  PAST_DATE_DEFAULT_VALUE,
  PRESENT_DEFAULT_VALUE,
  shouldRenderPresentValue,
  shouldRenderRangeValue,
  shouldRenderRelativeDateValue,
  shouldRenderSpecificDateValue,
  validateDateConditionLine,
};
