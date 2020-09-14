import React from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import { useControlledFormInputCondition } from '../../hooks';

const FallbackConditionLine: React.FC<ConditionLineProps> = ({
  lineNumber,
}) => {
  const { watch } = useFormContext();
  const { formName, getFormValue } = useControlledFormInputCondition<boolean>(
    lineNumber
  );
  const getConditionValues = () => watch(`content.conditions[${lineNumber}]`);

  return (
    <div className={'AknGrid-bodyCell AknRule'}>
      {JSON.stringify(getConditionValues())}
      {Object.keys(getConditionValues()).map((key: string) => (
        <Controller
          as={<span hidden />}
          name={formName(key)}
          defaultValue={getFormValue(key)}
          key={key}
        />
      ))}
    </div>
  );
};

export { FallbackConditionLine };
