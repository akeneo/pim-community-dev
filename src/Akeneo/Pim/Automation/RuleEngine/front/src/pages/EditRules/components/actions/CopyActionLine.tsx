import React from 'react';
import { CopyAction } from '../../../../models/actions';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useRegisterConst } from '../../hooks/useRegisterConst';
import { ActionGrid, ActionLeftSide, ActionRightSide, ActionTitle } from "./ActionLine";
import { useTranslate } from "../../../../dependenciesTools/hooks";
import { AttributeLocaleScopeSelector } from "./attribute";

type Props = {
  action: CopyAction;
} & ActionLineProps;

const CopyActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  handleDelete,
  locales,
  scopes,
}) => {
  const translate = useTranslate();
  useRegisterConst(`content.actions[${lineNumber}].type`, 'copy');

  return (
    <ActionTemplate
      title={translate('pimee_catalog_rule.form.edit.actions.copy.title')}
      helper={translate('pimee_catalog_rule.form.edit.actions.copy.helper')}
      legend={translate('pimee_catalog_rule.form.edit.actions.copy.helper')}
      handleDelete={handleDelete}>
      <ActionGrid>
        <ActionLeftSide>
          <ActionTitle>
            {translate('pimee_catalog_rule.form.edit.actions.copy.select_source')}
          </ActionTitle>
          <AttributeLocaleScopeSelector
            attributeCode={action.from_field}
            attributeFormName={`content.actions[${lineNumber}].from_field`}
            attributeId={`edit-rules-action-${lineNumber}-from-field`}
            attributeLabel={`${translate(
              'pimee_catalog_rule.form.edit.fields.attribute'
            )} ${translate('pim_common.required_label')}`}
            attributePlaceholder={''}
            scopeId={`edit-rules-action-${lineNumber}-from-scope`}
            scopeFormName={`content.actions[${lineNumber}.from_scope`}
            localeId={`edit-rules-action-${lineNumber}-from-locale`}
            localeFormName={`content.actions[${lineNumber}.from_locale`}
            locales={locales}
            scopes={scopes}
          />
        </ActionLeftSide>
        <ActionRightSide>
          <ActionTitle>
            {translate('pimee_catalog_rule.form.edit.actions.copy.select_target')}
          </ActionTitle>
          <AttributeLocaleScopeSelector
            attributeCode={action.to_field}
            attributeFormName={`content.actions[${lineNumber}].to_field`}
            attributeId={`edit-rules-action-${lineNumber}-to-field`}
            attributeLabel={`${translate(
              'pimee_catalog_rule.form.edit.fields.attribute'
            )} ${translate('pim_common.required_label')}`}
            attributePlaceholder={''}
            scopeId={`edit-rules-action-${lineNumber}-to-scope`}
            scopeFormName={`content.actions[${lineNumber}.to_scope`}
            localeId={`edit-rules-action-${lineNumber}-to-locale`}
            localeFormName={`content.actions[${lineNumber}.to_locale`}
            locales={locales}
            scopes={scopes}
          />
        </ActionRightSide>
      </ActionGrid>
    </ActionTemplate>
  );
};

export { CopyActionLine };
