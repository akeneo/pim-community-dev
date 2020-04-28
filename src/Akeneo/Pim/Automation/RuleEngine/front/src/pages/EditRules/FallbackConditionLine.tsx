import React from 'react';
import { FallbackCondition } from '../../models/FallbackCondition';
import { ConditionLineProps } from './ConditionLineProps';
import { useValueInitialization } from "./hooks/useValueInitialization";

const FallbackConditionLine: React.FC<ConditionLineProps> = ({
  condition,
  lineNumber,
}) => {
  const fallBackCondition = condition as FallbackCondition;

  useValueInitialization(`content.conditions[${lineNumber}]`, fallBackCondition.json);

  return <div>{JSON.stringify(fallBackCondition.json)}</div>;
};

export { FallbackConditionLine };
