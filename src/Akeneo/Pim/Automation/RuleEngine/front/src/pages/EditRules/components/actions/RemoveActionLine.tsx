import React from 'react';
import { RemoveAction } from '../../../../models/actions/RemoveAction';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { FallbackField } from '../FallbackField';

type Props = {
  action: RemoveAction;
} & ActionLineProps;

const RemoveActionLine: React.FC<Props> = ({
  translate,
  lineNumber,
  action,
  handleDelete,
}) => {
  const values: any = {
    type: 'remove',
    field: action.field,
    items: action.items,
  };

  if (action.locale) {
    values.locale = action.locale;
  }

  if (action.scope) {
    values.scope = action.scope;
  }

  if (action.includeChildren) {
    values.includeChildren = action.includeChildren;
  }

  useValueInitialization(`content.actions[${lineNumber}]`, values);

  return (
    <ActionTemplate
      translate={translate}
      title='Unknown Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      {/* It is not translated since it is temporary. */}
      {`The value${action.items.length > 1 ? 's' : ''} ${action.items.map((item: string) => `"${item}"`).join(
        ', '
      )} ${action.items.length > 1 ? 'are' : 'is'} removed from `}
      <FallbackField
        field={action.field}
        scope={action.scope}
        locale={action.locale}
      />
      .
    </ActionTemplate>
  );
};

export { RemoveActionLine };
