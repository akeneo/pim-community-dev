import React from "react";
import { useFormContext } from 'react-hook-form';
import { ConditionErrorLine } from "./style";

type Props = {
  lineNumber: number;
}

const ConditionLineErrors: React.FC<Props> = ({
  lineNumber
}) => {
  const { errors } = useFormContext();
  const conditionErrors: {[fieldName: string]: {type: string; message: string}} = errors?.content?.conditions?.[lineNumber] || {};
  const messages = Object.values(conditionErrors).map(fieldError => fieldError.message);

  return (
    <ConditionErrorLine>
      {messages.map((message, i) => {
        return <li key={i}>{message}</li>
      })}
    </ConditionErrorLine>
  );
};

export { ConditionLineErrors }
