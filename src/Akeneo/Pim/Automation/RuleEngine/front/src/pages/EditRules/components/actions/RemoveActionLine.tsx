import React from 'react';
import { RemoveAction } from '../../../../models/actions';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { FallbackField } from '../FallbackField';
import { useRegisterConsts } from "../../hooks/useRegisterConst";

type Props = {
  action: RemoveAction;
} & ActionLineProps;

const RemoveActionLine: React.FC<Props> = ({
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

  if (action.include_children) {
    values.includeChildren = action.include_children;
  }

  useRegisterConsts(values, `content.actions[${lineNumber}]`);

  return (
    <ActionTemplate
      title='Remove Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      <div className='AknGrid AknGrid--unclickable'>
        <div className='AknGrid-bodyRow AknGrid-bodyRow--highlight'>
          <div className='AknGrid-bodyCell'>
            {/* It is not translated since it is temporary. */}
            The value{action.items.length > 1 && 's'}&nbsp;
            <span className='AknRule-attribute'>
              {action.items.join(', ')}
              {action.include_children && ' and children'}
            </span>
            &nbsp;
            {action.items.length > 1 || action.include_children ? 'are' : 'is'}
            &nbsp;removed from&nbsp;
            <FallbackField
              field={action.field}
              scope={action.scope}
              locale={action.locale}
            />
            .
          </div>
        </div>
      </div>
    </ActionTemplate>
  );
};

export { RemoveActionLine };
