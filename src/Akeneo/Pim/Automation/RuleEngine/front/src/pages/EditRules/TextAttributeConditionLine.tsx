import React from "react";
import {Translate} from "../../dependenciesTools";
import {TextAttributeCondition, TextAttributeOperators} from "../../models/TextAttributeCondition";
import {InputText} from "../../components/InputText";
import {Operator} from "../../models/Operator";

type Props = {
  register: any,
  condition: TextAttributeCondition,
  lineNumber: number,
  translate: Translate,
}

const TextAttributeConditionLine: React.FC<Props> = ({ register, condition, lineNumber, translate }) => {
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
    </div>
  );
};

export { TextAttributeConditionLine }
