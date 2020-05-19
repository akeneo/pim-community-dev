import React from 'react';
import { AddAction } from '../../../../models/actions/AddAction';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { FallbackField } from '../FallbackField';

type Props = {
  action: AddAction;
} & ActionLineProps;

const AddActionLine: React.FC<Props> = ({
  translate,
  lineNumber,
  action,
  handleDelete,
}) => {
  const values: any = {
    type: 'add',
    field: action.field,
    items: action.items,
  };

  if (action.locale) {
    values.locale = action.locale;
  }

  if (action.scope) {
    values.scope = action.scope;
  }

  useValueInitialization(`content.actions[${lineNumber}]`, values);

  return (
    <ActionTemplate
      translate={translate}
      title='Add Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      {/* It is not translated since it is temporary. */}
      The value{action.items.length > 1 && 's'}&nbsp;
      <span className='AknRule-attribute'>{action.items.join(', ')}</span>
      {action.items.length > 1 ? ' are' : ' is'}
      &nbsp;added to&nbsp;
      <FallbackField
        field={action.field}
        scope={action.scope}
        locale={action.locale}
      />
      .
    </ActionTemplate>
  );
};

export { AddActionLine };
