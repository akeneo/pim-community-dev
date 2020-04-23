import React from 'react';
import { FallbackCondition } from '../../models/FallbackCondition';
import { ConditionLineProps } from './ConditionLineProps';

type Props = {
  condition: FallbackCondition;
} & ConditionLineProps;

const FallbackConditionLine: React.FC<Props> = ({ condition }) => {
  return <div>{JSON.stringify(condition.json)}</div>;
};

export { FallbackConditionLine };
