import React from 'react';
import { useFormContext } from 'react-hook-form';
import { ClearAction } from '../../../../models/actions/ClearAction';
import { AttributeCode } from '../../../../models';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { AttributeSelector } from '../../../../components/Selectors/AttributeSelector';

type Props = {
  action: ClearAction;
} & ActionLineProps;

const ClearActionLine: React.FC<Props> = ({
  translate,
  lineNumber,
  action,
  handleDelete,
  currentCatalogLocale,
}) => {
  const { watch, setValue } = useFormContext();

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

  const getFieldFormValue: () => AttributeCode | null = () =>
    watch(`content.actions[${lineNumber}].field`);

  const setFieldFormValue = (value: AttributeCode | null) =>
    setValue(`content.actions[${lineNumber}].field`, value);

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
            <AttributeSelector
              id={`edit-rules-action-${lineNumber}-field`}
              label='Attribute (required)'
              currentCatalogLocale={currentCatalogLocale}
              value={getFieldFormValue()}
              onChange={setFieldFormValue}
              placeholder='Attribute (required)'
            />
          </div>
        </div>
      </div>
    </ActionTemplate>
  );
};

export { ClearActionLine };
