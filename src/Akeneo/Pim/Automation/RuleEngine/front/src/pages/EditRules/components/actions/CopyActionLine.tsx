import React from 'react';
import { CopyAction } from '../../../../models/actions/CopyAction';
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
    fromField: action.fromField,
    toField: action.toField,
  };

  if (action.fromLocale) {
    values.fromLocale = action.fromLocale;
  }

  if (action.fromScope) {
    values.fromScope = action.fromScope;
  }

  if (action.toLocale) {
    values.toLocale = action.toLocale;
  }

  if (action.toScope) {
    values.toScope = action.toScope;
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
