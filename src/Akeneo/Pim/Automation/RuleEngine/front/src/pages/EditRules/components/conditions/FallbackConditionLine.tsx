import React from 'react';
import { Controller } from 'react-hook-form';
import { FallbackCondition } from '../../../../models/conditions';
import { ConditionLineProps } from './ConditionLineProps';
import {useControlledFormInputCondition} from "../../hooks";

type FallbackConditionLineProps = ConditionLineProps & {
  condition: FallbackCondition;
};

const FallbackConditionLine: React.FC<FallbackConditionLineProps> = ({
  condition,
  lineNumber,
}) => {
  const {
    formName,
    getFormValue,
  } = useControlledFormInputCondition<boolean>(lineNumber);

  return (
    <div className={'AknGrid-bodyCell AknRule'}>
      {JSON.stringify(condition)}
      {Object.keys(condition).forEach((key: string) => (
        <Controller
          as={<span hidden />}
          name={formName('key')}
          defaultValue={getFormValue(key)}
          key={key}
        />
      ))}
    </div>
  );
};

export { FallbackConditionLine, FallbackConditionLineProps };
