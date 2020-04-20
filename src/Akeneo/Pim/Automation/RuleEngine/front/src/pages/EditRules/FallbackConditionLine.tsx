import React from "react";
import {FallbackCondition} from "../../models/FallbackCondition";
import {Translate} from "../../dependenciesTools/provider/applicationDependenciesProvider.type";

type Props = {
  register: any;
  condition: FallbackCondition,
  lineNumber: number,
  translate: Translate,
}

const FallbackConditionLine: React.FC<Props> = ({ condition }) => {
  return (
    <div>
      {JSON.stringify(condition.json)}
    </div>
  );
};

export { FallbackConditionLine }
