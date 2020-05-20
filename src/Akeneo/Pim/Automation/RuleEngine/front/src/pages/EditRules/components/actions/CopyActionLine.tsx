import React from 'react';
import { CopyAction } from '../../../../models/actions';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { FallbackField } from '../FallbackField';

type Props = {
  action: CopyAction;
} & ActionLineProps;

const CopyActionLine: React.FC<Props> = ({
  translate,
  lineNumber,
  action,
  handleDelete,
}) => {
  const values: any = {
    type: 'copy',
    from_field: action.fromField,
    to_field: action.toField,
  };

  if (action.fromLocale) {
    values.from_locale = action.fromLocale;
  }
  if (action.fromScope) {
    values.from_scope = action.fromScope;
  }
  if (action.toLocale) {
    values.to_locale = action.toLocale;
  }
  if (action.toScope) {
    values.to_scope = action.toScope;
  }
  useValueInitialization(`content.actions[${lineNumber}]`, values);

  return (
    <ActionTemplate
      translate={translate}
      title='Copy Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      <FallbackField
        field={action.fromField}
        scope={action.fromScope}
        locale={action.fromLocale}
      />
      {/* It is not translated since it is temporary. */}
      &nbsp;is copied into&nbsp;
      <FallbackField
        field={action.toField}
        scope={action.toScope}
        locale={action.toLocale}
      />
      .
    </ActionTemplate>
  );
};

export { CopyActionLine };
