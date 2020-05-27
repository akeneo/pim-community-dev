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
  useValueInitialization(`content.actions[${lineNumber}]`, values, {}, [
    action,
  ]);

  return (
    <ActionTemplate
      translate={translate}
      title='Concatenate Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      <div className='AknGrid AknGrid--unclickable'>
        <div className='AknGrid-bodyRow AknGrid-bodyRow--highlight'>
          <div className='AknGrid-bodyCell'>
            {action.from
              .map((field: ProductField, key: number) => (
                <FallbackField
                  key={key}
                  field={field.field}
                  scope={field.scope || null}
                  locale={field.locale || null}
                />
              ))
              .reduce((prev: JSX.Element, curr: JSX.Element): any => [
                prev,
                ', ',
                curr,
              ])}
            &nbsp;are concatenated into&nbsp;
            <FallbackField
              field={action.to.field}
              scope={action.to.scope || null}
              locale={action.to.locale || null}
            />
            .
          </div>
        </div>
      </div>
    </ActionTemplate>
  );
};

export { ConcatenateActionLine };
