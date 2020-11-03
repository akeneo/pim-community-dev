import {useCallback} from 'react';
import {
  isAnEmptyOperator,
  isARangeOperator,
  isNotARangeOperator,
  formatPastFutureDate,
  defaultDateTypeOperatorsValue,
} from './dateConditionLines.utils';
import {Operator} from '../../../../../models/Operator';
import {
  DateTypeOptionIds,
  isDateBetweenValue,
  DateValue,
  DateStandardValue,
  TimePeriodOptionsIds,
} from './dateConditionLines.type';

type SetDateType = (dateType: DateTypeOptionIds) => void;
type SetFormValue = (value: DateValue) => void;

const handleOperatorChange = (
  setDateType: SetDateType,
  setValueFormValue: SetFormValue
) => (newOperator: Operator, currentOperator: Operator) => {
  if (isAnEmptyOperator(newOperator)) {
    setDateType(DateTypeOptionIds.NONE);
    setValueFormValue('');
  } else if (
    /* 
        If the user switch the operator between to not between (or the contrary),
        we want to keep the value. 
        So operator is the new value and get the current one from the form (getOperatorFormValue).
      */
    isARangeOperator(newOperator)
  ) {
    if (isNotARangeOperator(currentOperator)) {
      setValueFormValue(new Array(2).fill(''));
    }
    setDateType(DateTypeOptionIds.NONE);
  } else {
    /* 
        If the user switch from an operator with no date type, 
        we want to reset the value to a string !== string[] (between value) and the date type to SPECIFIC DATE
      */
    if (
      isARangeOperator(currentOperator) ||
      isAnEmptyOperator(currentOperator)
    ) {
      setValueFormValue('');
      setDateType(DateTypeOptionIds.SPECIFIC_DATE);
    }
  }
};

const handleValueChange = (setValueFormValue: SetFormValue) => (
  eventValue: DateStandardValue,
  currentValue: DateValue,
  betweenIndex = -1
) => {
  if (betweenIndex >= 0) {
    if (isDateBetweenValue(currentValue)) {
      currentValue[betweenIndex] = eventValue;
      setValueFormValue([...currentValue]);
    }
  } else {
    setValueFormValue(eventValue);
  }
};

const handlePeriodChange = (setValueFormValue: SetFormValue) => (
  timePeriod: TimePeriodOptionsIds,
  timeValue: string,
  dateType: DateTypeOptionIds
) => {
  if (
    dateType === DateTypeOptionIds.PAST_DATE ||
    dateType === DateTypeOptionIds.FUTURE_DATE
  ) {
    const relativeDate = formatPastFutureDate(timePeriod, timeValue, dateType);
    setValueFormValue(relativeDate);
  }
};

const handleDateTypeChange = (
  setDateType: SetDateType,
  setValueFormValue: SetFormValue
) => (dateType: DateTypeOptionIds) => {
  setValueFormValue(defaultDateTypeOperatorsValue[dateType]);
  setDateType(dateType);
};

const useDateConditionHandlers = (
  setDateType: SetDateType,
  setValueFormValue: SetFormValue
) => {
  const onChangeOperator = useCallback(
    handleOperatorChange(setDateType, setValueFormValue),
    [setDateType, setValueFormValue]
  );

  const onChangeValue = useCallback(handleValueChange(setValueFormValue), [
    setValueFormValue,
  ]);

  const onPeriodChange = useCallback(handlePeriodChange(setValueFormValue), [
    setValueFormValue,
  ]);

  const onDateTypeChange = useCallback(
    handleDateTypeChange(setDateType, setValueFormValue),
    [setValueFormValue, setDateType]
  );

  return {
    onChangeOperator,
    onChangeValue,
    onDateTypeChange,
    onPeriodChange,
  };
};

export {useDateConditionHandlers};
