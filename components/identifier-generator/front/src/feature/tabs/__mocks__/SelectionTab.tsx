import React from 'react';
import {CONDITION_NAMES, Conditions, IdentifierGenerator, Target} from '../../models';

type SelectionTabProps = {
  generator: IdentifierGenerator;
  target: Target;
  onChange: (conditions: Conditions) => void;
};

const SelectionTab: React.FC<SelectionTabProps> = ({generator, onChange}) => {
  const handleChange = () => {
    onChange([{type: CONDITION_NAMES.ENABLED, value: false}]);
  };

  return (
    <>
      SelectionTabMock
      <div>{JSON.stringify(generator.conditions)}</div>
      <button onClick={handleChange}>Update selection</button>
    </>
  );
};

export {SelectionTab};
