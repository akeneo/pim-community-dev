import React from 'react';
import { FallbackAction } from '../../../models/FallbackAction';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from '../ActionLineProps';
import { useFormContext } from 'react-hook-form';

type Props = {
  action: FallbackAction;
} & ActionLineProps;

const FallbackActionLine: React.FC<Props> = ({
  translate,
  lineNumber,
  action,
  handleDelete,
}) => {
  const { register, getValues, setValue } = useFormContext();

  const initializeValue = (field: string, value: any): void => {
    const key = `content.actions[${lineNumber}].${field}`;
    register(key);
    if (undefined === getValues()[key]) {
      setValue(key, value);
    }
  }
  Object.keys(action.json).forEach((field: string) => {
    initializeValue(field, action.json[field]);
  })

  return (
    <ActionTemplate
      translate={translate}
      title='Unknown Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      srOnly='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}
    >
      {JSON.stringify(action.json)}
    </ActionTemplate>
  );
};

export { FallbackActionLine };
