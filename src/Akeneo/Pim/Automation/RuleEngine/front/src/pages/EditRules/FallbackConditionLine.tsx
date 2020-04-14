import React from "react";
import {FallbackCondition} from "../../models/FallbackCondition";

type Props = {
  condition: FallbackCondition
}

const FallbackConditionLine: React.FC<Props> = ({ condition }) => {
  return (
    <div>
      {JSON.stringify(condition.json)}
    </div>
  );
};

export { FallbackConditionLine }
