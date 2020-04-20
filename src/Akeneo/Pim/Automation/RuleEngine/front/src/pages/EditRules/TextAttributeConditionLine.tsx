import React from "react";
import {Translate} from "../../dependenciesTools";
import {TextAttributeCondition, TextAttributeOperators} from "../../models/TextAttributeCondition";
import {InputText} from "../../components/InputText";

type Props = {
  register: any,
  condition: TextAttributeCondition,
  lineNumber: number,
  translate: Translate,
}

const TextAttributeConditionLine: React.FC<Props> = ({ register, condition, lineNumber, translate }) => {
  console.log(condition);
  return (
    <div>
      <span>{condition.attribute.code}</span>
      <select defaultValue={condition.operator}>
        {TextAttributeOperators.map((operator, i) => {
          return <option
            key={i}
          >
            {operator}
          </option>
        })}
      </select>
      <span>{condition.operator}</span>
      <span>
        <InputText
          id="edit-rules-input-toto"
          name={`conditions[${lineNumber}].value`}
          label={translate("pim_common.code")}
          ref={register}
        />
      </span>
    </div>
  );
};

export { TextAttributeConditionLine }
