import React from 'react';
import {Condition, Enabled} from '../../../models';

type EnabledLineProps = {
  condition: Enabled & {id: string};
  onChange: (condition: Condition & {id: string}) => void;
};

const EnabledLine: React.FC<EnabledLineProps> = ({condition, onChange}) => {
  const handleChange = () => {
    onChange({...condition, value: !condition.value});
  };

  return (
    <>
      EnabledLineMock
      <button onClick={handleChange}>Update value</button>
    </>
  );
};

export {EnabledLine};
