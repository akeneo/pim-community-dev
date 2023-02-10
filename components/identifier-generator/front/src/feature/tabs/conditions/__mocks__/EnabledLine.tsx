import React from 'react';
import {Condition, EnabledCondition} from '../../../models';
import {Button} from 'akeneo-design-system';

type EnabledLineProps = {
  condition: EnabledCondition & {id: string};
  onChange: (condition: Condition & {id: string}) => void;
  onDelete: (conditionId: string) => void;
};

const EnabledLine: React.FC<EnabledLineProps> = ({condition, onChange, onDelete}) => {
  const handleChange = () => {
    onChange({...condition, value: !condition.value});
  };

  const handleDelete = (conditionId: string) => () => {
    onDelete(conditionId);
  };

  return (
    <>
      EnabledLineMock
      <button onClick={handleChange}>Update value</button>
      <Button onClick={handleDelete(condition.id)}>Delete Enabled</Button>
    </>
  );
};

export {EnabledLine};
