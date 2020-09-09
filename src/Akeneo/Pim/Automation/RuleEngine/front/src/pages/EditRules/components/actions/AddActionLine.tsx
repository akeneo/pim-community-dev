import React from 'react';
import { Controller } from 'react-hook-form';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { FallbackField } from '../FallbackField';
import { useControlledFormInputAction } from '../../hooks';

const AddActionLine: React.FC<ActionLineProps> = ({
  lineNumber,
  handleDelete,
}) => {
  const { formName, getFormValue } = useControlledFormInputAction<boolean>(
    lineNumber
  );
  const items = getFormValue('items');

  return (
    <ActionTemplate
      title='Add Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}
      lineNumber={lineNumber}>
      <div className='AknGrid AknGrid--unclickable'>
        <div className='AknGrid-bodyRow AknGrid-bodyRow--highlight'>
          <div className='AknGrid-bodyCell'>
            <Controller
              as={<span hidden />}
              name={formName('type')}
              defaultValue={'add'}
            />
            <Controller
              as={<span hidden />}
              name={formName('field')}
              defaultValue={getFormValue('field')}
            />
            <Controller
              as={<span hidden />}
              name={formName('items')}
              defaultValue={getFormValue('items')}
            />
            {getFormValue('locale') && (
              <Controller
                as={<span hidden />}
                name={formName('locale')}
                defaultValue={getFormValue('locale')}
              />
            )}
            {getFormValue('scope') && (
              <Controller
                as={<span hidden />}
                name={formName('scope')}
                defaultValue={getFormValue('scope')}
              />
            )}
            {/* It is not translated since it is temporary. */}
            The value{items.length > 1 && 's'}&nbsp;
            <span className='AknRule-attribute'>{items.join(', ')}</span>
            &nbsp;
            {items.length > 1 ? ' are' : ' is'}
            &nbsp;added to&nbsp;
            <FallbackField
              field={getFormValue('field')}
              scope={getFormValue('scope')}
              locale={getFormValue('locale')}
            />
            .
          </div>
        </div>
      </div>
    </ActionTemplate>
  );
};

export { AddActionLine };
