import React from 'react';
import { CopyAction } from '../../../../models/actions';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { FallbackField } from '../FallbackField';
import { useRegisterConsts } from '../../hooks/useRegisterConst';

type Props = {
  action: CopyAction;
} & ActionLineProps;

const CopyActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  handleDelete,
}) => {
  const values: any = {
    type: 'copy',
    from_field: action.from_field,
    to_field: action.to_field,
  };

  if (action.from_locale) {
    values.from_locale = action.from_locale;
  }
  if (action.from_scope) {
    values.from_scope = action.from_scope;
  }
  if (action.to_locale) {
    values.to_locale = action.to_locale;
  }
  if (action.to_scope) {
    values.to_scope = action.to_scope;
  }
  useRegisterConsts(values, `content.actions[${lineNumber}]`);

  return (
    <ActionTemplate
      title='Copy Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      <div className='AknGrid AknGrid--unclickable'>
        <div className='AknGrid-bodyRow AknGrid-bodyRow--highlight'>
          <div className='AknGrid-bodyCell'>
            <FallbackField
              field={action.from_field}
              scope={action.from_scope}
              locale={action.from_locale}
            />
            {/* It is not translated since it is temporary. */}
            &nbsp;is copied into&nbsp;
            <FallbackField
              field={action.to_field}
              scope={action.to_scope}
              locale={action.to_locale}
            />
            .
          </div>
        </div>
      </div>
    </ActionTemplate>
  );
};

export { CopyActionLine };
