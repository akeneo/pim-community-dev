import React from "react";
import styled from "styled-components";
import {TextAttributeCondition, TextAttributeOperators} from "../../models/TextAttributeCondition";
import {InputText} from "../../components/InputText";
import {Operator} from "../../models/Operator";
import {ConditionLineProps} from "./ConditionLineProps";
import {Locale} from "../../models";
import {Scope} from "../../models";

type Props = {
  condition: TextAttributeCondition,
} & ConditionLineProps;

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

const TextAttributeConditionLine: React.FC<Props> = ({
    register,
    condition,
    lineNumber,
    translate,
    locales,
    scopes,
    currentCatalogLocale
  }) => {
  const [operator, setOperator] = React.useState<Operator>(condition.operator);
  const [scopeCode, setScopeCode] = React.useState<string | undefined>(condition.scope);

  const getScopeLabel = (scope: Scope): string => {
    return scope.labels[currentCatalogLocale] || `[${scope.code}]`;
  }

  const computeLocales = (): Locale[] => {
    return !condition.attribute.scopable
      ? locales
      : scopeCode ? scopes[scopeCode].locales : []
      ;
  };

  const translateOperator = (operator: string): string => {
    const label = translate('pimee_catalog_rule.form.edit.conditions.operators.' + operator);

    return label.charAt(0).toUpperCase() + label.slice(1);
  }

  return (
    <div>
      <FieldColumn className={"AknGrid-bodyCell--highlight"}>
        {condition.attribute.labels[currentCatalogLocale] || '[' + condition.attribute.code + ']'}
        <input type="hidden" name={`content.conditions[${lineNumber}].field`} ref={register}/>
      </FieldColumn>
      <OperatorColumn>
        <select
          name={`content.conditions[${lineNumber}].operator`}
          data-testid={`edit-rules-input-${lineNumber}-operator`}
          ref={register}
          onChange={(event) => {
            setOperator(event.target.value as Operator);
          }}
        >
          {TextAttributeOperators.map((operator, i) => {
            return <option key={i} value={operator}>
              {translateOperator(operator)}
            </option>
          })}
        </select>
      </OperatorColumn>
      <ValueColumn>
        {!([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(operator) && (
          <InputText
            data-testid={`edit-rules-input-${lineNumber}-value`}
            name={`content.conditions[${lineNumber}].value`}
            label={translate("pim_common.code")}
            ref={register}
          >&nbsp;</InputText>
        )}
      </ValueColumn>
      <ScopeColumn>
        {condition.attribute.scopable &&
        <select
          ref={register}
          data-testid={`edit-rules-input-${lineNumber}-scope`}
          name={`content.conditions[${lineNumber}].scope`}
          onChange={(event) => {
            setScopeCode(event.target.value);
          }}
        >
          {Object.values(scopes).map((scope: Scope, i: number) => {
            return <option key={i} value={scope.code}>{getScopeLabel(scope)}</option>
          })}
        </select>
        }
      </ScopeColumn>
      <LocaleColumn>
        {condition.attribute.localizable &&
        <select
          ref={register}
          data-testid={`edit-rules-input-${lineNumber}-locale`}
          name={`content.conditions[${lineNumber}].locale`}
        >
          {computeLocales().map((locale: Locale, i: number) => {
            return <option key={i} value={locale.code}>{locale.label}</option>
          })}
        </select>
        }
      </LocaleColumn>
    </div>
  );
};

export { TextAttributeConditionLine }
