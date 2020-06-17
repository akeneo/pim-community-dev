import React from 'react';
import { AddAction } from '../../../../models/actions/';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { FallbackField } from '../FallbackField';
import { useRegisterConsts } from '../../hooks/useRegisterConst';

type Props = {
  action: AddAction;
} & ActionLineProps;

const AddActionLine: React.FC<Props> = ({
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

  useRegisterConsts(values, `content.actions[${lineNumber}]`);

  return (
    <ActionTemplate
      title='Add Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      <div className='AknGrid AknGrid--unclickable'>
        <div className='AknGrid-bodyRow AknGrid-bodyRow--highlight'>
          <div className='AknGrid-bodyCell'>
            {/* It is not translated since it is temporary. */}
            The value{action.items.length > 1 && 's'}&nbsp;
            <span className='AknRule-attribute'>{action.items.join(', ')}</span>
            &nbsp;
            {action.items.length > 1 ? ' are' : ' is'}
            &nbsp;added to&nbsp;
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

export { AddActionLine };
