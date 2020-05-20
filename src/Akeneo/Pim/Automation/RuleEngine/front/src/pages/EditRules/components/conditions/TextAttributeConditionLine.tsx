import React from 'react';
import styled from 'styled-components';
import { useFormContext } from 'react-hook-form';
import {
  TextAttributeCondition,
  TextAttributeOperators,
} from '../../../../models/TextAttributeCondition';
import { Operator } from '../../../../models/Operator';
import { ConditionLineProps } from './ConditionLineProps';
import { Locale } from '../../../../models';
import { InputText } from '../../../../components/Inputs';
import { ScopeSelector } from '../../../../components/Selectors/ScopeSelector';
import { LocaleSelector } from '../../../../components/Selectors/LocaleSelector';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { useValueInitialization } from '../../hooks/useValueInitialization';

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
  const { register, watch, setValue } = useFormContext();

  const getOperatorFormValue: () => Operator = () => {
    return watch(`content.conditions[${lineNumber}].operator`);
  };
  const getScopeFormValue: () => string = () => {
    return watch(`content.conditions[${lineNumber}].scope`);
  };

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

  const getLocaleFormValue: () => string = () => {
    return watch(`content.conditions[${lineNumber}].locale`);
  };

  const shouldDisplayValue: () => boolean = () => {
    return !([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
      getOperatorFormValue()
    );
  };

  useValueInitialization(`content.conditions[${lineNumber}]`, {
    field: condition.field,
    operator: condition.operator,
    value: condition.value,
    scope: condition.scope,
    locale: condition.locale,
  });

  const setValueFormValue = (value: string | null) => {
    setValue(`content.conditions[${lineNumber}].value`, value);
  };

  const setScopeFormValue = (value: string) => {
    setValue(`content.conditions[${lineNumber}].scope`, value);
    if (
      !getAvailableLocales()
        .map(locale => locale.code)
        .includes(getLocaleFormValue())
    ) {
      setLocaleFormValue(null);
    }
  };

  const setLocaleFormValue = (value: string | null) => {
    setValue(`content.conditions[${lineNumber}].locale`, value);
  };

  const setOperatorFormValue = (value: Operator) => {
    setValue(`content.conditions[${lineNumber}].operator`, value);
    if (!shouldDisplayValue()) {
      setValueFormValue(null);
    }
  };

  return (
    <div>
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
          />
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
          />
        )}
      </LocaleColumn>
    </div>
  );
};

export { TextAttributeConditionLine, TextAttributeConditionLineProps };
