import React from 'react';
import {Conditions, Target} from '../../models';

type SelectionTabProps = {
  conditions: Conditions;
  target: Target;
};

const SelectionTab: React.FC<SelectionTabProps> = ({conditions}) => {
  return (
    <>
      SelectionTabMock
      <div>{JSON.stringify(conditions)}</div>
    </>
  );
};

export {SelectionTab};
