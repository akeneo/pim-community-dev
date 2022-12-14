import React from 'react';
import {CONDITION_NAMES, Conditions, Target} from '../../models';

type SelectionTabProps = {
  conditions: Conditions;
  target: Target;
  onChange: (conditions: Conditions) => void;
};

const SelectionTab: React.FC<SelectionTabProps> = ({conditions, onChange}) => {
  const handleChange = () => {
    onChange([{type: CONDITION_NAMES.ENABLED, value: false}]);
  };

  return (
    <>
      SelectionTabMock
      <div>{JSON.stringify(conditions)}</div>
      <button onClick={handleChange}>Update selection</button>
    </>
  );
};

export {SelectionTab};
