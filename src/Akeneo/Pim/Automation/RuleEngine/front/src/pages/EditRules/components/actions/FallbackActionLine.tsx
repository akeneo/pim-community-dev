import React from 'react';
import {Controller, useFormContext} from 'react-hook-form';
import {ActionTemplate} from './ActionTemplate';
import {ActionLineProps} from './ActionLineProps';
import {useControlledFormInputAction} from '../../hooks';

const FallbackActionLine: React.FC<ActionLineProps> = ({
  lineNumber,
  handleDelete,
}) => {
  const {watch} = useFormContext();
  const {formName, getFormValue} = useControlledFormInputAction<boolean>(
    lineNumber
  );
  const getActionValues = () => watch(`content.actions[${lineNumber}]`);

  return (
    <ActionTemplate
      title='Unknown Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}
      lineNumber={lineNumber}>
      <div className='AknGrid AknGrid--unclickable'>
        <div className='AknGrid-bodyRow AknGrid-bodyRow--highlight'>
          <div className='AknGrid-bodyCell'>
            <div
              style={{
                fontFamily:
                  'Courier, "MS Courier New", Prestige, "Everson Mono"',
              }}>
              {JSON.stringify(getActionValues())}
              {Object.keys(getActionValues() ?? {}).map((key: string) => (
                <Controller
                  as={<span hidden />}
                  name={formName(key)}
                  defaultValue={getFormValue(key)}
                  key={key}
                />
              ))}
            </div>
          </div>
        </div>
      </div>
    </ActionTemplate>
  );
};

export {FallbackActionLine};
