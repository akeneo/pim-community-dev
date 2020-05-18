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
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { ScopeSelector } from '../../../../components/Selectors/ScopeSelector';
import { LocaleSelector } from '../../../../components/Selectors/LocaleSelector';

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
  const { register, getValues, setValue } = useFormContext();
  const [scopeCode, setScopeCode] = React.useState<string | undefined>(
    condition.scope
  );

  register({ name: `content.conditions[${lineNumber}].field` });
  register({ name: `content.conditions[${lineNumber}].operator` });
  if (condition.attribute.scopable) {
    register({ name: `content.conditions[${lineNumber}].scope` });
  }
  if (condition.attribute.localizable) {
    register({ name: `content.conditions[${lineNumber}].locale` });
  }

  const getFormOperator = (): Operator =>
    getValues()[`content.conditions[${lineNumber}].operator`];
  const getFormLocale = (): string =>
    getValues()[`content.conditions[${lineNumber}].locale`] || '';
  const getFormScope = (): string =>
    getValues()[`content.conditions[${lineNumber}].scope`] || '';
  const valueMustBeSet = (): boolean =>
    ![Operator.IS_EMPTY, Operator.IS_NOT_EMPTY].includes(getFormOperator());

  const [displayValueInput, setDisplayValueInput] = React.useState<boolean>(
    valueMustBeSet()
  );

  const handleOperatorChange = (operator: Operator) => {
    setValue(`content.conditions[${lineNumber}].operator`, operator);
    const actualFormValue = getValues()[
      `content.conditions[${lineNumber}].value`
    ];
    setValue(
      `content.conditions[${lineNumber}].value`,
      valueMustBeSet() ? actualFormValue || condition.value || '' : null
    );
    setDisplayValueInput(valueMustBeSet());
  };

  const computeLocales = (): Locale[] => {
    return !condition.attribute.scopable
      ? locales
      : scopeCode
      ? scopes[scopeCode].locales
      : [];
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
          currentOperator={getFormOperator()}
          availableOperators={TextAttributeOperators}
          translate={translate}
          onSelectorChange={(value: string): void => {
            handleOperatorChange(value as Operator);
          }}
        />
      </OperatorColumn>
      <ValueColumn>
        {displayValueInput && (
          <InputText
            data-testid={`edit-rules-input-${lineNumber}-value`}
            name={`content.conditions[${lineNumber}].value`}
            label={translate('pim_common.code')}
            ref={register}
            hiddenLabel={true}>
            &nbsp;
          </InputText>
        )}
      </ValueColumn>
      <ScopeColumn>
        {condition.attribute.scopable && (
          <ScopeSelector
            id={`edit-rules-input-${lineNumber}-scope`}
            label='Scope'
            hiddenLabel={true}
            currentScopeCode={getFormScope()}
            availableScopes={Object.values(scopes)}
            onSelectorChange={(value: string): void => {
              setValue(`content.conditions[${lineNumber}].scope`, value);
              setScopeCode(value);
            }}
            currentCatalogLocale={currentCatalogLocale}
          />
        )}
      </ScopeColumn>
      <LocaleColumn>
        {condition.attribute.localizable && (
          <LocaleSelector
            id={`edit-rules-input-${lineNumber}-locale`}
            label='Locale'
            hiddenLabel={true}
            currentLocaleCode={getFormLocale()}
            availableLocales={computeLocales()}
            onSelectorChange={(value: string): void => {
              setValue(`content.conditions[${lineNumber}].locale`, value);
            }}
          />
        )}
      </LocaleColumn>
    </div>
  );
};

export { TextAttributeConditionLine, TextAttributeConditionLineProps };
