import React from 'react';
import { Controller } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import {
  ConditionLineErrorsContainer,
  ConditionLineFormAndErrorsContainer,
  ConditionLineFormContainer,
  FieldColumn,
  LocaleColumn,
  OperatorColumn,
  ScopeColumn,
  ValueColumn,
} from './style';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { CompletenessOperators } from '../../../../models/conditions';
import { useControlledFormInputCondition } from '../../hooks';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { Operator } from '../../../../models/Operator';
import { InputNumberWithHelper } from '../../../../components/Inputs';
import { LineErrors } from '../LineErrors';
import { ScopeSelector } from '../../../../components/Selectors/ScopeSelector';
import { LocaleSelector } from '../../../../components/Selectors/LocaleSelector';
import { Locale } from '../../../../models';

const CompletenessConditionLine: React.FC<ConditionLineProps> = ({
  lineNumber,
  currentCatalogLocale,
  locales,
  scopes,
}) => {
  const translate = useTranslate();
  const {
    fieldFormName,
    operatorFormName,
    getOperatorFormValue,
    valueFormName,
    getValueFormValue,
    getScopeFormValue,
    localeFormName,
    getLocaleFormValue,
    scopeFormName,
    isFormFieldInError,
  } = useControlledFormInputCondition<string[]>(lineNumber);

  const getAvailableLocales = (): Locale[] => {
    const scopeCode = getScopeFormValue();
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].locales;
    }
    return locales;
  };

  return (
    <ConditionLineFormAndErrorsContainer className='AknGrid-bodyCell'>
      <ConditionLineFormContainer>
        <Controller
          as={<input type='hidden' />}
          name={fieldFormName}
          defaultValue='completeness'
        />
        <FieldColumn
          className={'AknGrid-bodyCell--highlight'}
          title={translate('pim_common.completeness')}>
          {translate('pim_common.completeness')}
        </FieldColumn>
        <OperatorColumn>
          <Controller
            as={OperatorSelector}
            availableOperators={CompletenessOperators}
            data-testid={`edit-rules-input-${lineNumber}-operator`}
            hiddenLabel
            name={operatorFormName}
            defaultValue={getOperatorFormValue() ?? Operator.EQUALS}
            value={getOperatorFormValue() || ''}
          />
        </OperatorColumn>
        <ValueColumn small>
          <Controller
            as={InputNumberWithHelper}
            data-testid={`edit-rules-input-${lineNumber}-value`}
            name={valueFormName}
            label={translate('pimee_catalog_rule.rule.value')}
            hiddenLabel={true}
            defaultValue={getValueFormValue()}
            rules={{
              required: translate(
                'pimee_catalog_rule.exceptions.required_value'
              ),
            }}
            hasError={isFormFieldInError('value')}
            helper='%'
          />
        </ValueColumn>
        <ScopeColumn
          className={
            isFormFieldInError('scope') ? 'select2-container-error' : ''
          }>
          <Controller
            allowClear={false}
            as={ScopeSelector}
            availableScopes={Object.values(scopes)}
            currentCatalogLocale={currentCatalogLocale}
            data-testid={`edit-rules-input-${lineNumber}-scope`}
            hiddenLabel
            name={scopeFormName}
            defaultValue={getScopeFormValue()}
            value={getScopeFormValue()}
            rules={{
              required: translate(
                'pimee_catalog_rule.exceptions.required_scope_completeness'
              ),
            }}
          />
        </ScopeColumn>
        <LocaleColumn
          className={
            isFormFieldInError('locale') ? 'select2-container-error' : ''
          }>
          <Controller
            as={LocaleSelector}
            data-testid={`edit-rules-input-${lineNumber}-locale`}
            hiddenLabel
            availableLocales={getAvailableLocales()}
            defaultValue={getLocaleFormValue()}
            value={getLocaleFormValue()}
            allowClear={false}
            name={localeFormName}
            rules={{
              required: translate(
                'pimee_catalog_rule.exceptions.required_locale_completeness'
              ),
            }}
          />
        </LocaleColumn>
      </ConditionLineFormContainer>
      <ConditionLineErrorsContainer>
        <LineErrors lineNumber={lineNumber} type='conditions' />
      </ConditionLineErrorsContainer>
    </ConditionLineFormAndErrorsContainer>
  );
};

export { CompletenessConditionLine };
