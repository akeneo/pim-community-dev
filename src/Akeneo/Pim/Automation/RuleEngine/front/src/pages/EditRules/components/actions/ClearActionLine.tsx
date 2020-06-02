import React from 'react';
import { useFormContext } from 'react-hook-form';
import { ClearAction } from '../../../../models/actions';
import { AttributeCode } from '../../../../models';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { AttributeSelector } from '../../../../components/Selectors/AttributeSelector';
import { ActionTitle } from './ActionLine';

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
      title={translate(
        'pimee_catalog_rule.form.edit.actions.clear_attribute.title'
      )}
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      <ActionTitle>
        {translate(
          'pimee_catalog_rule.form.edit.actions.clear_attribute.subtitle'
        )}
      </ActionTitle>
      <div className={'AknFormContainer'}>
        <AttributeSelector
          id={`edit-rules-action-${lineNumber}-field`}
          label={`${translate(
            'pimee_catalog_rule.form.edit.fields.attribute'
          )} ${translate('pim_common.required_label')}`}
          currentCatalogLocale={currentCatalogLocale}
          value={getFieldFormValue()}
          onChange={setFieldFormValue}
          placeholder={translate(
            'pimee_catalog_rule.form.edit.actions.clear_attribute.subtitle'
          )}
        />
      </div>
    </ActionTemplate>
  );
};

export { ClearActionLine };
