import React from 'react';
import { ConcatenateAction } from '../../../../models/actions/ConcatenateAction';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { FallbackField } from '../FallbackField';

type Props = {
  action: ConcatenateAction;
} & ActionLineProps;

const ConcatenateActionLine: React.FC<Props> = ({
  translate,
  lineNumber,
  action,
  handleDelete,
}) => {
  const toField: any = { field: fromField.field };

  if (toField.locale) {
    value.locale = toField.locale;
  }

  if (toField.scope) {
    value.scope = toField.scope;
  }

  const values: any = {
    type: 'concatenate',
    from: action.from.map((fromField: Field) => {
      const value: any = { field: fromField.field };

      if (fromField.locale) {
        value.locale = fromField.locale;
      }

      if (fromField.scope) {
        value.scope = fromField.scope;
      }

      return value;
    }),
    to: toField,
  };

  useValueInitialization(`content.actions[${lineNumber}]`, values);

  // TODO templating
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
  )
};

export { ConcatenateActionLine };
