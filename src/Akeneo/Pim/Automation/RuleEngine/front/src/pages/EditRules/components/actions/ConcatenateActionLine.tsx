import React from 'react';
import { ConcatenateAction } from '../../../../models/actions';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { FallbackField } from '../FallbackField';
import { ProductField } from '../../../../models/actions';

type Props = {
  action: ConcatenateAction;
} & ActionLineProps;

const ConcatenateActionLine: React.FC<Props> = ({
  translate,
  lineNumber,
  action,
  handleDelete,
}) => {
  const values: any = {
    type: 'concatenate',
    from: action.from,
    to: action.to,
  };

  useValueInitialization(`content.actions[${lineNumber}]`, values);

  return (
    <ActionTemplate
      translate={translate}
      title='Concatenate Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      {action.from.map((field: ProductField, key: number) => (
        <React.Fragment key={key}>
          <FallbackField
            field={field.field}
            scope={field.scope}
            locale={field.locale}
          />
          {key < action.from.length - 1 && ', '}
        </React.Fragment>
      ))}
      &nbsp;are concatenated into&nbsp;
      <FallbackField
        field={action.to.field}
        scope={action.to.scope}
        locale={action.to.locale}
      />
      .
    </ActionTemplate>
  );
};

export { ConcatenateActionLine };
