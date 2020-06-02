import React from 'react';
import { ClearAction } from '../../../../models/actions';
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

  useValueInitialization(`content.actions[${lineNumber}]`, values, {}, [
    action,
  ]);

  return (
    <ActionTemplate
      translate={translate}
      title='Clear Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      <div className='AknGrid AknGrid--unclickable'>
        <div className='AknGrid-bodyRow AknGrid-bodyRow--highlight'>
          <div className='AknGrid-bodyCell'>
            <FallbackField
              field={action.field}
              scope={action.scope}
              locale={action.locale}
            />
            {/* It is not translated since it is temporary. */}
            &nbsp;is cleared.
          </div>
        </div>
      </div>
    </ActionTemplate>
  );
};

export { ClearActionLine };
