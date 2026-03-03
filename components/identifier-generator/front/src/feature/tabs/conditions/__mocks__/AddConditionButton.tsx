import React from 'react';
import {Condition, CONDITION_NAMES} from '../../../models';

type AddConditionButtonProps = {
  onAddCondition: (condition: Condition) => void;
};

const AddConditionButton: React.FC<AddConditionButtonProps> = ({onAddCondition}) => {
  const handleAddCondition = () => {
    onAddCondition({type: CONDITION_NAMES.ENABLED});
  };

  return (
    <>
      AddConditionButtonMock
      <button onClick={handleAddCondition}>Add condition</button>
    </>
  );
};

export {AddConditionButton};
