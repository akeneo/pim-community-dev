import React, { forwardRef } from 'react';
import styled from 'styled-components';
import { Operator } from '../../../../../models/Operator';
import {
  InputDate,
  InputDateType,
  InputRelativeDate,
} from '../../../../../components';

import {
  getTimeValueFromValue,
  getTimePeriodFromValue,
  shouldRenderRangeValue,
  shouldRenderPresentValue,
  shouldRenderSpecificDateValue,
  shouldRenderRelativeDateValue,
} from './dateConditionLines.utils';

import {
  DateValue,
  DateTypeOptionIds,
  TimePeriodOption,
  isDateBetweenValue,
  isDateStandardValue,
} from './dateConditionLines.type';
import { Translate } from '../../../../../dependenciesTools';

type DateAttributeValueProps = {
  currentOperator: Operator;
  currentValue: DateValue;
  dateType: DateTypeOptionIds;
  handlePeriodChange: (timePeriod: any, timeValue: string) => void;
  handleValueChange: (
    event: React.ChangeEvent<HTMLInputElement>,
    betweenIndex?: number
  ) => void;
  inputDateType: InputDateType;
  lineNumber: number;
  timePeriodOptions?: TimePeriodOption[];
  translate: Translate;
};

// Color is not present in theme ATM (discussed with the UI team)
const CustomInputDate = styled(InputDate)`
  color: #515d6d;
`;

const DateAttributeValue = forwardRef<
  HTMLInputElement,
  DateAttributeValueProps
>(
  (
    {
      currentValue,
      currentOperator,
      lineNumber,
      dateType,
      translate,
      handleValueChange,
      handlePeriodChange,
      timePeriodOptions,
      inputDateType,
    },
    forwardedRef: React.Ref<HTMLInputElement>
  ) => {
    if (isDateBetweenValue(currentValue)) {
      if (shouldRenderRangeValue(currentOperator, dateType)) {
        return (
          <>
            <CustomInputDate
              type={inputDateType}
              className='AknTextField'
              data-testid={`date-input-from-${lineNumber}`}
              hiddenLabel
              id={`date-input-from-${lineNumber}`}
              label={translate('pimee_catalog_rule.form.date.label.date_from')}
              onChange={event => handleValueChange(event, 0)}
              ref={forwardedRef}
              value={currentValue[0]}
              style={{ marginRight: '20px' }}
            />
            <CustomInputDate
              type={inputDateType}
              className='AknTextField'
              data-testid={`date-input-to-${lineNumber}`}
              hiddenLabel
              id={`date-input-to-${lineNumber}`}
              label={translate('pimee_catalog_rule.form.date.label.date_to')}
              onChange={event => handleValueChange(event, 1)}
              value={currentValue[1]}
            />
          </>
        );
      }
    }

    if (isDateStandardValue(currentValue)) {
      if (shouldRenderPresentValue(dateType, currentValue)) {
        return null;
      }
      if (shouldRenderSpecificDateValue(dateType, currentValue)) {
        return (
          <CustomInputDate
            type={inputDateType}
            className='AknTextField'
            data-testid={`date-input-${lineNumber}`}
            hiddenLabel
            id={`date-input-${lineNumber}`}
            label={translate('pimee_catalog_rule.form.date.label.date')}
            onChange={handleValueChange}
            ref={forwardedRef}
            value={currentValue}
          />
        );
      }
      if (
        shouldRenderRelativeDateValue(dateType, currentValue) &&
        timePeriodOptions
      ) {
        return (
          <InputRelativeDate
            currentTimePeriod={getTimePeriodFromValue(currentValue)}
            currentTimeValue={getTimeValueFromValue(currentValue)}
            lineNumber={lineNumber}
            onPeriodChange={handlePeriodChange}
            ref={forwardedRef}
            timePeriodOptions={timePeriodOptions}
            translateLabel={translate}
          />
        );
      }
    }
    return null;
  }
);

DateAttributeValue.displayName = 'DateAttributeValue';

export { DateAttributeValue };
