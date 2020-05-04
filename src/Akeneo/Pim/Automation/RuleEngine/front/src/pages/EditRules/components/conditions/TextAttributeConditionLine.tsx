import React from 'react';
import styled from 'styled-components';
import { useFormContext } from 'react-hook-form';
import {
  TextAttributeCondition,
  TextAttributeOperators,
} from '../../../../models/TextAttributeCondition';
import { Operator } from '../../../../models/Operator';
import { ConditionLineProps } from '../../ConditionLineProps';
import { Locale } from '../../../../models';
import { Scope } from '../../../../models';
import { InputText } from '../../../../components/Inputs';

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

const TextAttributeConditionLine: React.FC<ConditionLineProps> = ({
  condition,
  lineNumber,
  translate,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const { register } = useFormContext();
  const textAttributeCondition = condition as TextAttributeCondition;
  const [operator, setOperator] = React.useState<Operator>(
    textAttributeCondition.operator
  );
  const [scopeCode, setScopeCode] = React.useState<string | undefined>(
    textAttributeCondition.scope
  );

  const getScopeLabel = (scope: Scope): string => {
    return scope.labels[currentCatalogLocale] || `[${scope.code}]`;
  };

  const computeLocales = (): Locale[] => {
    return !textAttributeCondition.attribute.scopable
      ? locales
      : scopeCode
      ? scopes[scopeCode].locales
      : [];
  };

  const translateOperator = (operator: string): string => {
    const label = translate(
      'pimee_catalog_rule.form.edit.conditions.operators.' + operator
    );

    return label.charAt(0).toUpperCase() + label.slice(1);
  };

  return (
    <div>
      <FieldColumn className={'AknGrid-bodyCell--highlight'}>
        {textAttributeCondition.attribute.labels[currentCatalogLocale] ||
          '[' + textAttributeCondition.attribute.code + ']'}
        <input
          type='hidden'
          name={`content.conditions[${lineNumber}].field`}
          ref={register}
        />
      </FieldColumn>
      <OperatorColumn>
        <select
          name={`content.conditions[${lineNumber}].operator`}
          data-testid={`edit-rules-input-${lineNumber}-operator`}
          ref={register}
          onChange={event => {
            setOperator(event.target.value as Operator);
          }}>
          {TextAttributeOperators.map((operator, i) => {
            return (
              <option key={i} value={operator}>
                {translateOperator(operator)}
              </option>
            );
          })}
        </select>
      </OperatorColumn>
      <ValueColumn>
        {!([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
          operator
        ) && (
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
        {textAttributeCondition.attribute.scopable && (
          <select
            ref={register}
            data-testid={`edit-rules-input-${lineNumber}-scope`}
            name={`content.conditions[${lineNumber}].scope`}
            onChange={event => {
              setScopeCode(event.target.value);
            }}>
            {Object.values(scopes).map((scope: Scope, i: number) => {
              return (
                <option key={i} value={scope.code}>
                  {getScopeLabel(scope)}
                </option>
              );
            })}
          </select>
        )}
      </ScopeColumn>
      <LocaleColumn>
        {textAttributeCondition.attribute.localizable && (
          <select
            ref={register}
            data-testid={`edit-rules-input-${lineNumber}-locale`}
            name={`content.conditions[${lineNumber}].locale`}>
            {computeLocales().map((locale: Locale, i: number) => {
              return (
                <option key={i} value={locale.code}>
                  {locale.label}
                </option>
              );
            })}
          </select>
        )}
      </LocaleColumn>
    </div>
  );
};

export { TextAttributeConditionLine };
