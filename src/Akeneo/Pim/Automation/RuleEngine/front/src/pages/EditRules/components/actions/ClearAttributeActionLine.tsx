import React from 'react';
import {Controller, useFormContext} from 'react-hook-form';
import {ActionTemplate} from './ActionTemplate';
import {ActionLineProps} from './ActionLineProps';
import {ActionGrid, ActionTitle, ActionLeftSide} from './ActionLine';
import {AttributeLocaleScopeSelector} from './attribute/AttributeLocaleScopeSelector';
import {
  useTranslate,
  useBackboneRouter,
} from '../../../../dependenciesTools/hooks';
import {useControlledFormInputAction} from '../../hooks';
import {Attribute} from '../../../../models';
import {
  validateAttribute,
  useGetAttributeAtMount,
} from './attribute/attribute.utils';

const ClearAttributeActionLine: React.FC<ActionLineProps> = ({
  lineNumber,
  handleDelete,
  locales,
  scopes,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const {watch} = useFormContext();
  const [attribute, setAttribute] = React.useState<
    Attribute | null | undefined
  >(undefined);

  const {
    fieldFormName,
    typeFormName,
    setFieldFormValue,
    getFieldFormValue,
  } = useControlledFormInputAction<string>(lineNumber);
  watch(fieldFormName);
  useGetAttributeAtMount(getFieldFormValue(), router, attribute, setAttribute);

  const onAttributeChange = (attribute: Attribute | null) => {
    setAttribute(attribute);
    setFieldFormValue(attribute?.code);
  };

  return (
    <>
      <Controller
        name={fieldFormName}
        as={<span hidden />}
        defaultValue=''
        rules={{validate: validateAttribute(translate, router)}}
      />
      <Controller
        name={typeFormName}
        as={<span hidden />}
        defaultValue='clear'
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.clear_attribute.title'
        )}
        helper={translate('pimee_catalog_rule.form.helper.clear_attribute')}
        legend={translate('pimee_catalog_rule.form.helper.clear_attribute')}
        handleDelete={handleDelete}
        lineNumber={lineNumber}>
        <ActionGrid>
          <ActionLeftSide>
            <ActionTitle>
              {translate(
                'pimee_catalog_rule.form.edit.actions.clear_attribute.subtitle'
              )}
            </ActionTitle>
            <AttributeLocaleScopeSelector
              attribute={attribute}
              attributeId={`edit-rules-action-${lineNumber}-field`}
              attributeLabel={`${translate(
                'pimee_catalog_rule.form.edit.fields.attribute'
              )} ${translate('pim_common.required_label')}`}
              attributePlaceholder={translate(
                'pimee_catalog_rule.form.edit.actions.clear_attribute.subtitle'
              )}
              attributeCode={getFieldFormValue() ?? ''}
              scopeId={`edit-rules-action-${lineNumber}-scope`}
              scopes={scopes}
              localeId={`edit-rules-action-${lineNumber}-locale`}
              locales={locales}
              lineNumber={lineNumber}
              attributeFormName={fieldFormName}
              onAttributeCodeChange={onAttributeChange}
            />
          </ActionLeftSide>
        </ActionGrid>
      </ActionTemplate>
    </>
  );
};

export {ClearAttributeActionLine};
