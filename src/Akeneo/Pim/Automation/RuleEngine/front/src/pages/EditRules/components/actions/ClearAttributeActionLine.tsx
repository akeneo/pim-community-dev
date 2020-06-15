import React from 'react';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { ActionTitle } from './ActionLine';
import { ClearAttributeAction } from '../../../../models/actions';
import { AttributeLocaleScopeSelector } from './attribute/AttributeLocaleScopeSelector';
import { LineErrors } from '../LineErrors';
import { useRegisterConst } from '../../hooks/useRegisterConst';
import { useTranslate } from '../../../../dependenciesTools/hooks';

type Props = {
  action: ClearAttributeAction;
} & ActionLineProps;

const ClearAttributeActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  handleDelete,
  locales,
  scopes,
}) => {
  const translate = useTranslate();
  useRegisterConst(`content.actions[${lineNumber}].type`, 'clear');

  return (
    <ActionTemplate
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
        scopes={scopes}
        localeId={`edit-rules-action-${lineNumber}-locale`}
        localeFormName={`content.actions[${lineNumber}].locale`}
        locales={locales}
        localeValue={action.locale || undefined}
        scopeValue={action.scope || undefined}
      />
      <LineErrors lineNumber={lineNumber} type='actions' />
    </ActionTemplate>
  );
};

export { ClearAttributeActionLine };
