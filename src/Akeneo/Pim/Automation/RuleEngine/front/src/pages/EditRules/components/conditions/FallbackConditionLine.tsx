import React from 'react';
import { FallbackCondition } from '../../../../models/conditions';
import { ConditionLineProps } from './ConditionLineProps';
import { useRegisterConsts } from "../../hooks/useRegisterConst";

type FallbackConditionLineProps = ConditionLineProps & {
  condition: FallbackCondition;
};

const FallbackConditionLine: React.FC<FallbackConditionLineProps> = ({
  condition,
  lineNumber,
}) => {
  useRegisterConsts(condition.json, `content.conditions[${lineNumber}]`);

  return (
    <div className={'AknGrid-bodyCell'}>{JSON.stringify(condition.json)}</div>
  );
};

export { FallbackConditionLine, FallbackConditionLineProps };
