import React from 'react';
import { FallbackCondition } from '../../models/FallbackCondition';
import { ConditionLineProps } from './ConditionLineProps';
import { useFormContext } from 'react-hook-form';

const FallbackConditionLine: React.FC<ConditionLineProps> = ({
  condition,
  lineNumber,
}) => {
  const fallBackCondition = condition as FallbackCondition;
  const { register, getValues, setValue } = useFormContext();

  const initializeValue = (field: string, value: any): void => {
    const key = `content.conditions[${lineNumber}].${field}`;
    register(key);
    if (undefined === getValues()[key]) {
      setValue(key, value);
    }
  }
  Object.keys(fallBackCondition.json).forEach((field: string) => {
    initializeValue(field, fallBackCondition.json[field]);
  })

  return <div>{JSON.stringify(fallBackCondition.json)}</div>;
};

export { FallbackConditionLine };
