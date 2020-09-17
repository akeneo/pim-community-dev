import React, { useState } from 'react';
import { Controller } from 'react-hook-form';
import {
  dateSystemOperators,
  DateSystemCondition,
} from '../../../../../models/conditions';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { useControlledFormInputCondition } from '../../../hooks';
import { Operator } from '../../../../../models/Operator';

import {
  createDateTypeOptions,
  createTimePeriodCalendarOptions,
  createTimePeriodClockOptions,
  DateTypeOptionIds,
  DateValue,
} from './dateConditionLines.type';

import {
  getDateTypeFromValue,
  validateDateConditionLine,
} from './dateConditionLines.utils';

import { DateTemplateConditionLine } from './DateTemplateConditionLine';
import {
  ConditionLineErrorsContainer,
  ConditionLineFormAndErrorsContainer,
} from '../style';
import { LineErrors } from '../../LineErrors';
import { useDateConditionHandlers } from './dateConditionLines.hooks';

type Props = {
  condition: DateSystemCondition;
  lineNumber: number;
};

const DateSystemConditionLine: React.FC<Props> = ({
  condition,
  lineNumber,
}) => {
  const translate = useTranslate();
  const {
    fieldFormName,
    valueFormName,
    getOperatorFormValue,
    getValueFormValue,
    setValueFormValue,
  } = useControlledFormInputCondition<DateValue>(lineNumber);
  const [dateType, setDateType] = useState<DateTypeOptionIds>(
    getDateTypeFromValue(getValueFormValue() || '')
  );
  const currentOperator = getOperatorFormValue() || Operator.EQUALS;
  const dateTypeOptions = createDateTypeOptions(translate);
  const timePeriodOptions = [
    ...createTimePeriodCalendarOptions(translate),
    ...createTimePeriodClockOptions(translate),
  ];

  const {
    onChangeOperator,
    onChangeValue,
    onDateTypeChange,
    onPeriodChange,
  } = useDateConditionHandlers(setDateType, setValueFormValue);

  return (
    <ConditionLineFormAndErrorsContainer className={'AknGrid-bodyCell'}>
      <Controller
        as={<span hidden />}
        defaultValue={condition.field}
        name={fieldFormName}
      />
      <Controller
        as={<span hidden />}
        defaultValue={getValueFormValue()}
        name={valueFormName}
        rules={{
          validate: () =>
            validateDateConditionLine(
              getValueFormValue(),
              dateType,
              currentOperator,
              translate
            ),
        }}
      />
      <DateTemplateConditionLine
        availableOperators={dateSystemOperators}
        dateType={dateType}
        dateTypeOptions={dateTypeOptions}
        handleDateTypeChange={onDateTypeChange}
        handleOperatorChange={newOperator =>
          onChangeOperator(newOperator, currentOperator)
        }
        handlePeriodChange={(timePeriod, timeValue) =>
          onPeriodChange(timePeriod, timeValue, dateType)
        }
        handleValueChange={(event, betweenIndex) =>
          onChangeValue(event.target.value, getValueFormValue(), betweenIndex)
        }
        inputDateType='datetime-local'
        lineNumber={lineNumber}
        operator={currentOperator}
        timePeriodOptions={timePeriodOptions}
        title={translate(`pim_common.${condition.field}`)}
        value={getValueFormValue() || ''}
      />
      <ConditionLineErrorsContainer>
        <LineErrors lineNumber={lineNumber} type='conditions' />
      </ConditionLineErrorsContainer>
    </ConditionLineFormAndErrorsContainer>
  );
};

export { DateSystemConditionLine };
