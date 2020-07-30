import React from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { Operator } from '../../../../models/Operator';
import { Locale, LocaleCode } from '../../../../models';
import {
  getScopeValidation,
  ScopeSelector,
} from '../../../../components/Selectors/ScopeSelector';
import {
  getLocaleValidation,
  LocaleSelector,
} from '../../../../components/Selectors/LocaleSelector';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import {
  ConditionLineFormContainer,
  ConditionLineFormAndErrorsContainer,
  ConditionErrorLine,
  FieldColumn,
  LocaleColumn,
  OperatorColumn,
  ScopeColumn,
  ValueColumn,
} from './style';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';
import { LineErrors } from '../LineErrors';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { Attribute } from '../../../../models';

import { useControlledFormInputCondition } from '../../hooks';

const shouldDisplayValue: (operator: Operator) => boolean = operator =>
  !([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
    operator
  );

type AttributeConditionLineProps = {
  attribute?: Attribute | null;
  availableOperators: Operator[];
  currentCatalogLocale: LocaleCode;
  defaultOperator: Operator;
  field: string;
  lineNumber: number;
  locales: Locale[];
  scopes: IndexedScopes;
};

const AttributeConditionLine: React.FC<AttributeConditionLineProps> = ({
  attribute,
  availableOperators,
  children,
  currentCatalogLocale,
  defaultOperator,
  field,
  lineNumber,
  locales,
  scopes,
}) => {
  const translate = useTranslate();
  const { errors } = useFormContext();
  const {
    fieldFormName,
    operatorFormName,
    localeFormName,
    scopeFormName,
    getLocaleFormValue,
    getOperatorFormValue,
    getScopeFormValue,
  } = useControlledFormInputCondition<string[]>(lineNumber);

  const getAvailableLocales = (): Locale[] => {
    if (!attribute || !attribute.scopable) {
      return locales;
    }
    const scopeCode = getScopeFormValue();
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].locales;
    }
    return [];
  };

  const title =
    attribute && attribute.labels[currentCatalogLocale]
      ? attribute.labels[currentCatalogLocale]
      : `[${field}]`;

  if (attribute === undefined) {
    return (
      <div className='AknGrid-bodyCell'>
        <img
          src='/bundles/pimui/images//loader-V2.svg'
          alt={translate('pim_common.loading')}
        />
      </div>
    );
  }

  if (attribute === null) {
    return (
      <div className='AknGrid-bodyCell'>
        <ConditionErrorLine>
          {translate('pimee_catalog_rule.exceptions.unknown_attribute_code', {
            attributeCode: field,
          })}
        </ConditionErrorLine>
      </div>
    );
  }

  const isElementInError = (element: string): boolean =>
    typeof errors?.content?.conditions?.[lineNumber]?.[element] === 'object';

  return (
    <ConditionLineFormAndErrorsContainer className={'AknGrid-bodyCell'}>
      <ConditionLineFormContainer>
        <FieldColumn className={'AknGrid-bodyCell--highlight'} title={title}>
          {title}
        </FieldColumn>
        <Controller
          as={<input type='hidden' />}
          name={fieldFormName}
          defaultValue={field}
        />
        <OperatorColumn>
          <Controller
            as={OperatorSelector}
            availableOperators={availableOperators}
            data-testid={`edit-rules-input-${lineNumber}-operator`}
            defaultValue={getOperatorFormValue() ?? defaultOperator}
            hiddenLabel
            name={operatorFormName}
            value={getOperatorFormValue()}
          />
        </OperatorColumn>
        <ValueColumn>
          {shouldDisplayValue(getOperatorFormValue() ?? defaultOperator) &&
            children}
        </ValueColumn>
        {(attribute.scopable || getScopeFormValue()) && (
          <ScopeColumn
            className={
              isElementInError('scope') ? 'select2-container-error' : ''
            }>
            <Controller
              allowClear={!attribute.scopable}
              as={ScopeSelector}
              availableScopes={Object.values(scopes)}
              currentCatalogLocale={currentCatalogLocale}
              data-testid={`edit-rules-input-${lineNumber}-scope`}
              hiddenLabel
              name={scopeFormName}
              defaultValue={getScopeFormValue()}
              value={getScopeFormValue()}
              rules={getScopeValidation(
                attribute || null,
                scopes,
                translate,
                currentCatalogLocale
              )}
            />
          </ScopeColumn>
        )}
        {(attribute.localizable || getLocaleFormValue()) && (
          <LocaleColumn
            className={
              isElementInError('locale') ? 'select2-container-error' : ''
            }>
            <Controller
              as={LocaleSelector}
              data-testid={`edit-rules-input-${lineNumber}-locale`}
              hiddenLabel
              availableLocales={getAvailableLocales()}
              defaultValue={getLocaleFormValue()}
              value={getLocaleFormValue()}
              allowClear={!attribute.localizable}
              name={localeFormName}
              rules={getLocaleValidation(
                attribute || null,
                locales,
                getAvailableLocales(),
                getScopeFormValue(),
                translate,
                currentCatalogLocale
              )}
            />
          </LocaleColumn>
        )}
      </ConditionLineFormContainer>
      <LineErrors lineNumber={lineNumber} type='conditions' />
    </ConditionLineFormAndErrorsContainer>
  );
};

export { AttributeConditionLine };
