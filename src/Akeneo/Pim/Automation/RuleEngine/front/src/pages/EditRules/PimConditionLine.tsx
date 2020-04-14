import React from "react";
import {PimCondition} from "../../models/PimCondition";

type Props = {
  condition: PimCondition
}

const PimConditionLine: React.FC<Props> = ({ condition }) => {
  return (
    <div>
      <span>Field: {condition.field}</span>,
      <span>Operator: {condition.operator}</span>,
      <span>Value: {condition.value}</span>,
      <span>Scope: {condition.scope}</span>,
      <span>Locale: {condition.locale}</span>
    </div>
  );
};

export { PimConditionLine }
