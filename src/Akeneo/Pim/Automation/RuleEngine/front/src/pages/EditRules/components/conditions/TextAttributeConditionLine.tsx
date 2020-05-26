import React from 'react';
import styled from 'styled-components';
import { useFormContext, ErrorMessage } from 'react-hook-form';
import {
  TextAttributeCondition,
  TextAttributeOperators,
} from '../../../../models/TextAttributeCondition';
import { Operator } from '../../../../models/Operator';
import { ConditionLineProps } from './ConditionLineProps';
import { Locale, LocaleCode, ScopeCode } from '../../../../models';
import { InputText } from '../../../../components/Inputs';
import { ScopeSelector } from '../../../../components/Selectors/ScopeSelector';
import { LocaleSelector } from '../../../../components/Selectors/LocaleSelector';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { InputErrorMsg } from '../../../../components/InputErrorMsg';

const FieldColumn = styled.span`
  width: 100px;
  display: inline-block;
  padding: 0 2px;
  overflow: hidden;
`;

const OperatorColumn = styled.span`
  width: 150px;
  display: inline-block;
  padding: 0 2px;
`;

const ValueColumn = styled.span`
  width: 400px;
  display: inline-block;
  padding: 0 2px;
`;

const LocaleColumn = styled.span`
  width: 150px;
  display: inline-block;
  padding: 0 2px;
`;

const ScopeColumn = styled.span`
  width: 150px;
  display: inline-block;
  padding: 0 2px;
`;

type TextAttributeConditionLineProps = ConditionLineProps & {
  condition: TextAttributeCondition;
};

const TextAttributeConditionLine: React.FC<TextAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  translate,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const {
    register,
    watch,
    setValue,
    errors,
    triggerValidation,
  } = useFormContext();

  const getOperatorFormValue: () => Operator = () =>
    watch(`content.conditions[${lineNumber}].operator`);

  const getScopeFormValue: () => ScopeCode = () =>
    watch(`content.conditions[${lineNumber}].scope`);
  const getLocaleFormValue: () => LocaleCode = () =>
    watch(`content.conditions[${lineNumber}].locale`);

  const getAvailableLocales = (): Locale[] => {
    if (!condition.attribute.scopable) {
      return locales;
    }

    const scopeCode = getScopeFormValue();
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].locales;
    }

    return [];
  };

  const shouldDisplayValue: () => boolean = () => {
    return !([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
      getOperatorFormValue()
    );
  };

  useValueInitialization(
    `content.conditions[${lineNumber}]`,
    {
      field: condition.field,
      operator: condition.operator,
      value: condition.value,
      scope: condition.scope,
      locale: condition.locale,
    },
    {
      scope: condition.attribute.scopable
        ? { required: translate('pimee_catalog_rule.exceptions.required') }
        : {},
      locale: condition.attribute.localizable
        ? { required: translate('pimee_catalog_rule.exceptions.required') }
        : {},
    },
    [condition]
  );

  const setValueFormValue = (value: string | null) =>
    setValue(`content.conditions[${lineNumber}].value`, value);
  const setLocaleFormValue = (value: LocaleCode | null) => {
    setValue(`content.conditions[${lineNumber}].locale`, value);
    triggerValidation(`content.conditions[${lineNumber}].locale`);
  };

  const setScopeFormValue = (value: ScopeCode) => {
    setValue(`content.conditions[${lineNumber}].scope`, value);
    triggerValidation(`content.conditions[${lineNumber}].scope`);
    if (
      !getAvailableLocales()
        .map(locale => locale.code)
        .includes(getLocaleFormValue())
    ) {
      setLocaleFormValue(null);
    }
  };

  const setOperatorFormValue = (value: Operator) => {
    setValue(`content.conditions[${lineNumber}].operator`, value);
    if (!shouldDisplayValue()) {
      setValueFormValue(null);
    }
  };

  return (
    <div className={'AknGrid-bodyCell'}>
      <FieldColumn className={'AknGrid-bodyCell--highlight'}>
        {condition.attribute.labels[currentCatalogLocale] ||
          '[' + condition.attribute.code + ']'}
      </FieldColumn>
      <OperatorColumn>
        <OperatorSelector
          id={`edit-rules-input-${lineNumber}-operator`}
          label='Operator'
          hiddenLabel={true}
          availableOperators={TextAttributeOperators}
          translate={translate}
          value={getOperatorFormValue()}
          onChange={setOperatorFormValue}
        />
      </OperatorColumn>
      <ValueColumn>
        {shouldDisplayValue() && (
          <InputText
            data-testid={`edit-rules-input-${lineNumber}-value`}
            name={`content.conditions[${lineNumber}].value`}
            label={translate('pim_common.code')}
            ref={register}
            hiddenLabel={true}
          />
        )}
      </ValueColumn>
      <ScopeColumn>
        {condition.attribute.scopable && (
          <ScopeSelector
            id={`edit-rules-input-${lineNumber}-scope`}
            label='Scope'
            hiddenLabel={true}
            availableScopes={Object.values(scopes)}
            currentCatalogLocale={currentCatalogLocale}
            value={getScopeFormValue()}
            onChange={setScopeFormValue}
            translate={translate}>
            <ErrorMessage
              errors={errors}
              name={`content.conditions[${lineNumber}].scope`}>
              {({ message }) => <InputErrorMsg>{message}</InputErrorMsg>}
            </ErrorMessage>
          </ScopeSelector>
        )}
      </ScopeColumn>
      <LocaleColumn>
        {condition.attribute.localizable && (
          <LocaleSelector
            id={`edit-rules-input-${lineNumber}-locale`}
            label='Locale'
            hiddenLabel={true}
            availableLocales={getAvailableLocales()}
            value={getLocaleFormValue()}
            onChange={setLocaleFormValue}
            translate={translate}>
            <ErrorMessage
              errors={errors}
              name={`content.conditions[${lineNumber}].locale`}>
              {({ message }) => <InputErrorMsg>{message}</InputErrorMsg>}
            </ErrorMessage>
          </LocaleSelector>
        )}
      </LocaleColumn>
    </div>
  );
};

export { TextAttributeConditionLine, TextAttributeConditionLineProps };
