import React from 'react';
import { ClearAction } from '../../../../models/actions/ClearAction';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { FallbackField } from '../FallbackField';

type Props = {
  action: ClearAction;
} & ActionLineProps;

const ClearActionLine: React.FC<Props> = ({
  translate,
  lineNumber,
  action,
  handleDelete,
}) => {
  const values: any = {
    type: 'clear',
    field: action.field,
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
      title='Unknown Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      <FallbackField
        field={action.field}
        scope={action.scope}
        locale={action.locale}
      />
      {/* It is not translated since it is temporary. */}
      {' is cleared.'}
    </ActionTemplate>
  );
};

export { ClearActionLine };
