import React from 'react';
import { FallbackCondition } from '../../models/FallbackCondition';
import { ConditionLineProps } from './ConditionLineProps';

type FallbackConditionLineProps = {
  condition: FallbackCondition;
} & ConditionLineProps;

const FallbackConditionLine: React.FC<FallbackConditionLineProps> = ({ condition }) => {
  return <div>{JSON.stringify(condition.json)}</div>;
};

export { FallbackConditionLine, FallbackConditionLineProps };
