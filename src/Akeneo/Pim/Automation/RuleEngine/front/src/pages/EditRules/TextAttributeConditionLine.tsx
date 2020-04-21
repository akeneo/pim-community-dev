import React from "react";
import {TextAttributeCondition, TextAttributeOperators} from "../../models/TextAttributeCondition";
import {InputText} from "../../components/InputText";
import {Operator} from "../../models/Operator";
import {ConditionLineProps} from "./ConditionLineProps";
import {Locale} from "../../models";
import {Scope} from "../../models";

type Props = {
  condition: TextAttributeCondition,
} & ConditionLineProps;

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

  const getLocales = (): Locale[] => {
    if (!condition.attribute.scopable) {
      return locales;
    }

    return scopeCode ? scopes[scopeCode].locales : [];
  };

  return (
    <div>
      <span>
        {condition.attribute.labels[currentCatalogLocale] || '[' + condition.attribute.code + ']'}
        <input type="hidden" name={`content.conditions[${lineNumber}].field`} ref={register}/>
      </span>
      <select
        style={{'width': 150, 'display': 'inline-block'}} //TODO Style
        name={`content.conditions[${lineNumber}].operator`}
        ref={register}
        onChange={(event) => {
          setOperator(event.target.value as Operator);
        }}
      >
        {TextAttributeOperators.map((operator, i) => {
          return <option key={i} value={operator}>
            {translate('pimee_catalog_rule.form.edit.conditions.operators.' + operator)}
          </option>
        })}
      </select>
      {!([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(operator) && (
        <span
          style={{'width': 200, 'display': 'inline-block'}}
        >
          <InputText
              id="edit-rules-input-toto"
              name={`content.conditions[${lineNumber}].value`}
              label={translate("pim_common.code")}
              ref={register}
          >&nbsp;</InputText>
        </span>
      )}
      {condition.attribute.localizable &&
        <select
          style={{'width': 150, 'display': 'inline-block'}} //TODO Style
          ref={register}
          name={`content.conditions[${lineNumber}].locale`}
        >
          {getLocales().map((locale: Locale, i: number) => {
            return <option key={i} value={locale.code}>{locale.label}</option>
          })}
        </select>
      }
      {condition.attribute.scopable &&
        <select
          style={{'width': 150, 'display': 'inline-block'}} //TODO Style
          ref={register}
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
    </div>
  );
};

export { TextAttributeConditionLine }
