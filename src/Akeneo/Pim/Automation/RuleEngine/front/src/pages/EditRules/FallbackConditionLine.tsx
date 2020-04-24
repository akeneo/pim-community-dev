import React from 'react';
import { FallbackCondition } from '../../models/FallbackCondition';
import { ConditionLineProps } from './ConditionLineProps';

const FallbackConditionLine: React.FC<ConditionLineProps> = ({ condition }) => {
  const fallBackCondition = condition as FallbackCondition;

  return <div>{JSON.stringify(fallBackCondition.json)}</div>;
};

export { FallbackConditionLine };
