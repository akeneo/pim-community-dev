import React from 'react';
import { Controller } from 'react-hook-form';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { ActionTitle } from './ActionLine';
import { ClearAttributeAction } from '../../../../models/actions';
import { AttributeLocaleScopeSelector } from './attribute/AttributeLocaleScopeSelector';
import { LineErrors } from '../LineErrors';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { useControlledFormInputAction } from '../../hooks';

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
  const { fieldFormName, typeFormName } = useControlledFormInputAction<string>(
    lineNumber
  );

  return (
    <>
      <Controller
        name={fieldFormName}
        as={<input type='hidden' />}
        defaultValue=''
      />
      <Controller
        name={typeFormName}
        as={<input type='hidden' />}
        defaultValue='clear'
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.clear_attribute.title'
        )}
        helper={translate('pimee_catalog_rule.form.helper.clear_attribute')}
        legend={translate('pimee_catalog_rule.form.helper.clear_attribute')}
        handleDelete={handleDelete}>
        <ActionTitle>
          {translate(
            'pimee_catalog_rule.form.edit.actions.clear_attribute.subtitle'
          )}
        </ActionTitle>
        <AttributeLocaleScopeSelector
          attribute={null}
          attributeId={`edit-rules-action-${lineNumber}-field`}
          attributeLabel={`${translate(
            'pimee_catalog_rule.form.edit.fields.attribute'
          )} ${translate('pim_common.required_label')}`}
          attributePlaceholder={translate(
            'pimee_catalog_rule.form.edit.actions.clear_attribute.subtitle'
          )}
          attributeCode={action.field}
          scopeId={`edit-rules-action-${lineNumber}-scope`}
          scopes={scopes}
          localeId={`edit-rules-action-${lineNumber}-locale`}
          locales={locales}
          lineNumber={lineNumber}
          attributeFormName={fieldFormName}
        />
        <LineErrors lineNumber={lineNumber} type='actions' />
      </ActionTemplate>
    </>
  );
};

export { ClearAttributeActionLine };
