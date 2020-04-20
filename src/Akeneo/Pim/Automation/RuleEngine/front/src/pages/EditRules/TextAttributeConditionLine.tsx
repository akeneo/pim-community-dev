import React from "react";
import {TextAttributeCondition, TextAttributeOperators} from "../../models/TextAttributeCondition";
import {InputText} from "../../components/InputText";
import {Operator} from "../../models/Operator";
import {ConditionLineProps} from "./ConditionLineProps";
import {Locale} from "../../models/Locale";

type Props = {
  condition: TextAttributeCondition,
} & ConditionLineProps;

const TextAttributeConditionLine: React.FC<Props> = ({
    register,
    condition,
    lineNumber,
    translate,
    activatedLocales
  }) => {
  const [operator, setOperator] = React.useState<Operator>(condition.operator);

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
          defaultValue={condition.locale ? condition.locale.code : undefined}
          ref={register}
          name={`conditions[${lineNumber}].locale`}
        >
          {activatedLocales.map((locale: Locale, i: number) => {
            return <option key={i} value={locale.code}>{locale.label}</option>
          })}
        </select>
      }
    </div>
  );
};

export { TextAttributeConditionLine }
