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

  const getScopeLabel = (scope: Scope): string => {
    return scope.labels[currentCatalogLocale] || `[${scope.code}]`;
  }

  return (
    <div>
      <span>{condition.attribute.code}</span>
      <select
        defaultValue={operator}
        name={`conditions[${lineNumber}].operator`}
        ref={register}
        onChange={(event) => {
          setOperator(event.target.value as Operator);
        }}
      >
        {TextAttributeOperators.map((operator, i) => {
          return <option key={i}>{operator}</option>
        })}
      </select>
      {!([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(operator) && (
        <span>
          <InputText
              id="edit-rules-input-toto"
              name={`conditions[${lineNumber}].value`}
              label={translate("pim_common.code")}
              ref={register}
          />
        </span>
      )}
      {condition.attribute.localizable &&
        <select
          value={condition.locale ? condition.locale.code : undefined}
          ref={register}
          name={`conditions[${lineNumber}].locale`}
          onChange={() => {}}
        >
          {locales.map((locale: Locale, i: number) => {
            return <option key={i} value={locale.code}>{locale.label}</option>
          })}
        </select>
      }
      {condition.attribute.scopable &&
        <select
          value={condition.scope ? condition.scope.code : undefined}
          ref={register}
          name={`conditions[${lineNumber}].scope`}
          onChange={() => {}}
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
