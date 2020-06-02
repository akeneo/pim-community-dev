import React from 'react';
import { useFormContext } from 'react-hook-form';
import { AttributeCode, LocaleCode, ScopeCode } from '../../../../models';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { ActionTitle } from './ActionLine';
import { ClearAttributeAction } from '../../../../models/actions';
import { ActionLineErrors } from './ActionLineErrors';
import { AttributeLocaleScopeSelector } from './AttributeLocaleScopeSelector';

type Props = {
  action: ClearAttributeAction;
} & ActionLineProps;

const ClearAttributeActionLine: React.FC<Props> = ({
  translate,
  lineNumber,
  action,
  handleDelete,
  currentCatalogLocale,
}) => {
  const { watch, setValue, triggerValidation } = useFormContext();

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

  useValueInitialization(
    `content.actions[${lineNumber}]`,
    values,
    {
      field: {
        required: translate('pimee_catalog_rule.exceptions.required_attribute'),
      },
    },
    [action]
  );

  const getFieldFormValue: () => AttributeCode | null = () =>
    watch(`content.actions[${lineNumber}].field`);

  const setFieldFormValue = (value: AttributeCode | null) => {
    setValue(`content.actions[${lineNumber}].field`, value);
    triggerValidation(`content.actions[${lineNumber}].field`);
  };

  const setLocaleFormValue = (value: LocaleCode | null) => {
    setValue(`content.actions[${lineNumber}].locale`, value);
    triggerValidation(`content.actions[${lineNumber}].locale`);
  };
  const setScopeFormValue = (value: ScopeCode | null) => {
    setValue(`content.actions[${lineNumber}].scope`, value);
    triggerValidation(`content.actions[${lineNumber}].scope`);
  };

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
      <AttributeLocaleScopeSelector
        attributeId={`edit-rules-action-${lineNumber}-field`}
        scopeId={`edit-rules-action-${lineNumber}-scope`}
        localeId={`edit-rules-action-${lineNumber}-locale`}
        attributeLabel={`${translate(
          'pimee_catalog_rule.form.edit.fields.attribute'
        )} ${translate('pim_common.required_label')}`}
        currentCatalogLocale={currentCatalogLocale}
        attributeCode={getFieldFormValue()}
        onAttributeChange={setFieldFormValue}
        attributePlaceholder={translate(
          'pimee_catalog_rule.form.edit.actions.clear_attribute.subtitle'
        )}
        onLocaleChange={setLocaleFormValue}
        onScopeChange={setScopeFormValue}
        translate={translate}
        localeLabel={``}
        localePlaceholder={``}
        scopeLabel={``}
        scopePlaceholder={``}
      />
      <ActionLineErrors lineNumber={lineNumber} />
    </ActionTemplate>
  );
};

export { ClearAttributeActionLine };
