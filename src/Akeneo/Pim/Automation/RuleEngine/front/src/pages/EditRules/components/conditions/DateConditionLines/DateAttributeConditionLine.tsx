import React, { useState } from 'react';
import { Controller } from 'react-hook-form';
import { ConditionLineProps } from '../ConditionLineProps';
import { DateAttributeCondition } from '../../../../../models/conditions';
import { ConditionLineFormAndErrorsContainer } from '../style';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../../dependenciesTools/hooks';
import { Attribute, getAttributeLabel } from '../../../../../models';
import { useControlledFormInputCondition } from '../../../hooks';
import { Operator } from '../../../../../models/Operator';
import {
  createDateTypeOptions,
  createTimePeriodCalendarOptions,
  DateTypeOptionIds,
  DateValue,
} from './dateConditionLines.type';
import {
  getDateTypeFromValue,
  validateDateConditionLine,
  getDateAttributeOperatorsFromDateType,
} from './dateConditionLines.utils';
import { LineErrors } from '../../LineErrors';
import { useGetAttributeAtMount } from '../../actions/attribute/attribute.utils';
import { DateTemplateConditionLine } from './DateTemplateConditionLine';
import { useDateConditionHandlers } from './dateConditionLines.hooks';

type DateAttributeConditionLineProps = {
  condition: DateAttributeCondition;
} & ConditionLineProps;

const DateAttributeConditionLine: React.FC<DateAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const [dateAttribute, setDateAttribute] = useState<Attribute | null>();
  const translate = useTranslate();
  const router = useBackboneRouter();
  const {
    fieldFormName,
    valueFormName,
    getOperatorFormValue,
    getValueFormValue,
    setValueFormValue,
  } = useControlledFormInputCondition<DateValue>(lineNumber);
  const [dateType, setDateType] = useState<DateTypeOptionIds>(
    getDateTypeFromValue(getValueFormValue())
  );
  const currentOperator = getOperatorFormValue() || Operator.IS_EMPTY;
  const dateTypeOptions = createDateTypeOptions(translate);
  const timePeriodOptions = createTimePeriodCalendarOptions(translate);

  useGetAttributeAtMount(
    condition.field,
    router,
    dateAttribute,
    setDateAttribute
  );

  const {
    onChangeOperator,
    onChangeValue,
    onDateTypeChange,
    onPeriodChange,
  } = useDateConditionHandlers(setDateType, setValueFormValue);

  if (!dateAttribute) {
    return null;
  }

  const title = dateAttribute
    ? getAttributeLabel(dateAttribute, currentCatalogLocale)
    : `[${condition.field}]`;

  return (
    <ConditionLineFormAndErrorsContainer className={'AknGrid-bodyCell'}>
      <Controller
        as={<span hidden />}
        defaultValue={dateAttribute.code}
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
        availableOperators={getDateAttributeOperatorsFromDateType(dateType)}
        dateAttribute={dateAttribute}
        dateType={dateType}
        dateTypeOptions={dateTypeOptions}
        handleDateTypeChange={dateType => onDateTypeChange(dateType)}
        handleOperatorChange={newOperator =>
          onChangeOperator(newOperator, currentOperator)
        }
        handlePeriodChange={(timePeriod, timeValue) =>
          onPeriodChange(timePeriod, timeValue, dateType)
        }
        handleValueChange={(event, betweenIndex) =>
          onChangeValue(event.target.value, getValueFormValue(), betweenIndex)
        }
        inputDateType='date'
        lineNumber={lineNumber}
        locales={locales}
        operator={currentOperator}
        scopes={scopes}
        timePeriodOptions={timePeriodOptions}
        title={title}
        value={getValueFormValue()}
      />
      <LineErrors lineNumber={lineNumber} type='conditions' />
    </ConditionLineFormAndErrorsContainer>
  );
};

export { DateAttributeConditionLine };
