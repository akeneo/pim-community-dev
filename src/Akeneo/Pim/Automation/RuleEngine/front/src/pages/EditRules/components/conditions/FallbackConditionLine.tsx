import React from 'react';
import { FallbackCondition } from '../../../../models/conditions';
import { ConditionLineProps } from './ConditionLineProps';
import { useValueInitialization } from '../../hooks/useValueInitialization';

type FallbackConditionLineProps = ConditionLineProps & {
  condition: FallbackCondition;
};

const FallbackConditionLine: React.FC<FallbackConditionLineProps> = ({
  condition,
  lineNumber,
}) => {
  useValueInitialization(
    `content.conditions[${lineNumber}]`,
    condition.json,
    {},
    [condition]
  );

  return (
    <div className={'AknGrid-bodyCell'}>{JSON.stringify(condition.json)}</div>
  );
};

export { FallbackConditionLine, FallbackConditionLineProps };
