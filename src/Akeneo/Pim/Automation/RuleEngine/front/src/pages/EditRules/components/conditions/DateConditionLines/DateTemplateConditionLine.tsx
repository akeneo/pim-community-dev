import React, { useRef, useEffect } from 'react';
import styled from 'styled-components';
import { Controller, useFormContext } from 'react-hook-form';
import {
  useUserCatalogLocale,
  useTranslate,
} from '../../../../../dependenciesTools/hooks';
import {
  ConditionLineFormContainer,
  FieldColumn,
  OperatorColumn,
  ValueColumn,
  ScopeColumn,
  LocaleColumn,
} from '../style';
import {
  isNotAnEmptyOperator,
  isNotARangeOperator,
  isARangeOperator,
} from './dateConditionLines.utils';
import { OperatorSelector } from '../../../../../components/Selectors/OperatorSelector';
import { Select2SimpleSyncWrapper } from '../../../../../components';
import { DateAttributeValue } from './DateAttributeValue';
import { useControlledFormInputCondition } from '../../..';
import {
  DateValue,
  DateTypeOption,
  TimePeriodOption,
  DateTypeOptionIds,
  TimePeriodOptionsIds,
} from './dateConditionLines.type';
import { Operator } from '../../../../../models/Operator';
import {
  ScopeSelector,
  getScopeValidation,
} from '../../../../../components/Selectors/ScopeSelector';
import {
  LocaleSelector,
  getLocaleValidation,
} from '../../../../../components/Selectors/LocaleSelector';
import { Attribute, Locale } from '../../../../../models';
import { IndexedScopes } from '../../../../../repositories/ScopeRepository';

const StyledSelect2Wrapper = styled.div`
  width: 53%;
  padding-right: 20px;
`;

type Props = {
  availableOperators: Operator[];
  dateAttribute?: Attribute;
  dateType: DateTypeOptionIds;
  dateTypeOptions: DateTypeOption[];
  handleDateTypeChange: (dateType: any) => void;
  handleOperatorChange: (operator: Operator) => void;
  handlePeriodChange: (
    timePeriod: TimePeriodOptionsIds,
    timeValue: string
  ) => void;
  handleValueChange: (
    event: React.ChangeEvent<HTMLInputElement>,
    betweenIndex?: number
  ) => void;
  inputDateType: 'date' | 'datetime-local';
  lineNumber: number;
  operator: Operator;
  timePeriodOptions: TimePeriodOption[];
  title: string;
  value: DateValue;
  locales?: Locale[];
  scopes?: IndexedScopes;
};

const DateTemplateConditionLine: React.FC<Props> = ({
  availableOperators,
  dateAttribute,
  dateType,
  dateTypeOptions,
  handleDateTypeChange,
  handleOperatorChange,
  handlePeriodChange,
  handleValueChange,
  inputDateType,
  lineNumber,
  locales,
  operator,
  scopes,
  timePeriodOptions,
  title,
  value,
}) => {
  const refToInput = useRef<HTMLInputElement>(null);
  const translate = useTranslate();
  const locale = useUserCatalogLocale();
  const { errors } = useFormContext();
  const isElementInError = (element: string): boolean =>
    typeof errors?.content?.conditions?.[lineNumber]?.[element] === 'object';

  const {
    operatorFormName,
    getOperatorFormValue,
    scopeFormName,
    getScopeFormValue,
    localeFormName,
    getLocaleFormValue,
  } = useControlledFormInputCondition<DateValue>(lineNumber);

  useEffect(() => {
    if (
      refToInput &&
      (dateType === DateTypeOptionIds.FUTURE_DATE ||
        dateType === DateTypeOptionIds.PAST_DATE ||
        dateType === DateTypeOptionIds.SPECIFIC_DATE ||
        isARangeOperator(operator))
    ) {
      refToInput.current?.focus();
    }
  }, [dateType, operator]);

  const getAvailableLocales = (): Locale[] => {
    if (locales && (!dateAttribute || !dateAttribute.scopable)) {
      return locales;
    }
    const scopeCode = getScopeFormValue();
    if (scopes && scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].locales;
    }
    return [];
  };

  return (
    <ConditionLineFormContainer>
      <FieldColumn className={'AknGrid-bodyCell--highlight'}>
        {title}
      </FieldColumn>
      <OperatorColumn>
        <Controller
          as={OperatorSelector}
          availableOperators={availableOperators}
          data-testid={operatorFormName}
          defaultValue={operator}
          hiddenLabel
          label={translate('pimee_catalog_rule.form.date.label.operator')}
          name={operatorFormName}
          value={getOperatorFormValue()}
          onChange={([value]) => {
            handleOperatorChange(value);
            return value;
          }}
        />
      </OperatorColumn>
      {isNotAnEmptyOperator(operator) && (
        <ValueColumn>
          {isNotARangeOperator(operator) && (
            <StyledSelect2Wrapper>
              <Select2SimpleSyncWrapper
                data-testid={`date-type-${lineNumber}`}
                data={dateTypeOptions}
                dropdownCssClass={'operator-dropdown'}
                hiddenLabel
                hideSearch
                label={translate(
                  'pimee_catalog_rule.form.date.label.date_type'
                )}
                name={name}
                onChange={handleDateTypeChange}
                value={dateType}
              />
            </StyledSelect2Wrapper>
          )}
          <DateAttributeValue
            currentOperator={operator}
            currentValue={value}
            dateType={dateType}
            handlePeriodChange={handlePeriodChange}
            handleValueChange={handleValueChange}
            inputDateType={inputDateType}
            lineNumber={lineNumber}
            ref={refToInput}
            timePeriodOptions={timePeriodOptions}
            translate={translate}
            hasError={isElementInError('value')}
          />
        </ValueColumn>
      )}
      {dateAttribute && dateAttribute.scopable && scopes && (
        <ScopeColumn
          className={
            isElementInError('scope') ? 'select2-container-error' : ''
          }>
          <Controller
            allowClear={!dateAttribute.scopable}
            as={ScopeSelector}
            availableScopes={Object.values(scopes)}
            currentCatalogLocale={locale}
            data-testid={scopeFormName}
            defaultValue={getScopeFormValue()}
            hiddenLabel
            label={translate('pimee_catalog_rule.form.date.label.scope')}
            name={scopeFormName}
            rules={getScopeValidation(
              dateAttribute || null,
              scopes,
              translate,
              locale
            )}
            value={getScopeFormValue()}
          />
        </ScopeColumn>
      )}
      {dateAttribute && dateAttribute.localizable && locales && (
        <LocaleColumn
          className={
            isElementInError('scope') ? 'select2-container-error' : ''
          }>
          <Controller
            allowClear={!dateAttribute.localizable}
            as={LocaleSelector}
            availableLocales={getAvailableLocales()}
            defaultValue={getLocaleFormValue()}
            hiddenLabel
            label={translate('pimee_catalog_rule.form.date.label.locale')}
            name={localeFormName}
            data-testid={localeFormName}
            rules={getLocaleValidation(
              dateAttribute || null,
              locales,
              getAvailableLocales(),
              getScopeFormValue(),
              translate,
              locale
            )}
            value={getLocaleFormValue()}
          />
        </LocaleColumn>
      )}
    </ConditionLineFormContainer>
  );
};

export { DateTemplateConditionLine };
