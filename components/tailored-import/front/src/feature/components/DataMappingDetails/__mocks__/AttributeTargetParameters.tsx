import React from 'react';
import {AttributeTargetParametersProps} from '../AttributeTargetParameters';
import {getErrorsForPath} from '@akeneo-pim-community/shared';

const AttributeTargetParameters = ({
  children,
  target,
  onTargetChange,
  validationErrors,
}: AttributeTargetParametersProps) => {
  return (
    <>
      <h1>Attribute target parameters</h1>
      <button onClick={() => onTargetChange({...target, action_if_empty: 'clear'})}>Set source</button>
      {getErrorsForPath(validationErrors, '').map((error, index) => (
        <div key={index}>{error.messageTemplate}</div>
      ))}
      {children}
    </>
  );
};

export {AttributeTargetParameters};
