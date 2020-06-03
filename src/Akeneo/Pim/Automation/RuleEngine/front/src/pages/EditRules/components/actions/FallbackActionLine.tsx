import React from 'react';
import { FallbackAction } from '../../../../models/FallbackAction';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useRegisterConsts } from "../../hooks/useRegisterConst";

type Props = {
  action: FallbackAction;
} & ActionLineProps;

const FallbackActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  handleDelete,
}) => {
  useRegisterConsts(action.json, `content.actions[${lineNumber}]`);

  return (
    <ActionTemplate
      title='Unknown Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      <div className='AknGrid AknGrid--unclickable'>
        <div className='AknGrid-bodyRow AknGrid-bodyRow--highlight'>
          <div className='AknGrid-bodyCell'>
            <div
              style={{
                fontFamily:
                  'Courier, "MS Courier New", Prestige, "Everson Mono"',
              }}>
              {JSON.stringify(action.json)}
            </div>
          </div>
        </div>
      </div>
    </ActionTemplate>
  );
};

export { FallbackActionLine };
