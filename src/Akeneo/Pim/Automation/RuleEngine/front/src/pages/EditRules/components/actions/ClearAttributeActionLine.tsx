import React from 'react';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { ActionTitle } from './ActionLine';
import { ClearAttributeAction } from '../../../../models/actions';
import { ActionLineErrors } from './ActionLineErrors';
import { AttributeLocaleScopeSelector } from './AttributeLocaleScopeSelector';
import { useValueInitialization } from '../../hooks/useValueInitialization';

type Props = {
  action: ClearAttributeAction;
} & ActionLineProps;

const ClearAttributeActionLine: React.FC<Props> = ({
  translate,
  lineNumber,
  action,
  handleDelete,
  currentCatalogLocale,
  locales,
  scopes,
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

  useValueInitialization(
    `content.actions[${lineNumber}]`,
    { type: action.type },
    {},
    [action]
  );

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
        attributeLabel={`${translate(
          'pimee_catalog_rule.form.edit.fields.attribute'
        )} ${translate('pim_common.required_label')}`}
        attributePlaceholder={translate(
          'pimee_catalog_rule.form.edit.actions.clear_attribute.subtitle'
        )}
        attributeFormName={`content.actions[${lineNumber}].field`}
        attributeCode={action.field}
        scopeId={`edit-rules-action-${lineNumber}-scope`}
        scopeFormName={`content.actions[${lineNumber}].scope`}
        scopeCode={action.scope || null}
        scopes={scopes}
        localeId={`edit-rules-action-${lineNumber}-locale`}
        localeFormName={`content.actions[${lineNumber}].locale`}
        localeCode={action.locale || null}
        locales={locales}
        currentCatalogLocale={currentCatalogLocale}
        translate={translate}
      />
      <ActionLineErrors lineNumber={lineNumber} />
    </ActionTemplate>
  );
};

export { ClearAttributeActionLine };
