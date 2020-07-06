import React from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { SetAction } from '../../../../models/actions';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import {
  ActionGrid,
  ActionLeftSide,
  ActionRightSide,
  ActionTitle,
} from './ActionLine';
import {
  AttributeLocaleScopeSelector,
  MANAGED_ATTRIBUTE_TYPES,
} from './attribute';
import { Attribute } from '../../../../models';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { LineErrors } from '../LineErrors';
import { AttributeValue } from './attribute';
import { useControlledFormInputAction } from '../../hooks';

type Props = {
  action: SetAction;
} & ActionLineProps;

const SetActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  handleDelete,
  locales,
  scopes,
}) => {
  const translate = useTranslate();
  const [attribute, setAttribute] = React.useState<
    Attribute | null | undefined
  >(undefined);

  const {
    fieldFormName,
    typeFormName,
    valueFormName,
    getValueFormValue,
    setFieldFormValue,
    setValueFormValue,
    getFieldFormValue,
  } = useControlledFormInputAction<string | null>(lineNumber);
  // Watch is needed in this case to trigger a render at input
  const { watch } = useFormContext();
  watch(valueFormName);
  const onAttributeChange = (newAttribute: Attribute | null) => {
    /*
      onAttributeChange is called at mount to set the correct attribute.
      But we already know the correct field and the default value.
      So to avoid to lost the default value at mount we are checking the field value avoiding the null setter.
    */
    if (newAttribute && getFieldFormValue() !== newAttribute.code) {
      setValueFormValue(null);
    }
    setFieldFormValue(newAttribute ? newAttribute.code : '');
    setAttribute(newAttribute);
  };

  const isUnmanagedAttribute = () =>
    attribute && !(attribute.type in MANAGED_ATTRIBUTE_TYPES);

  return (
    <>
      <Controller name={fieldFormName} as={<span hidden />} defaultValue='' />
      <Controller name={typeFormName} as={<span hidden />} defaultValue='set' />
      <Controller
        name={valueFormName}
        as={<span hidden />}
        defaultValue={getValueFormValue()}
      />
      <ActionTemplate
        title='Set Action'
        helper='This feature is under development. Please use the import to manage your rules.'
        legend='This feature is under development. Please use the import to manage your rules.'
        handleDelete={handleDelete}>
        <LineErrors lineNumber={lineNumber} type='actions' />
        <ActionGrid>
          <ActionLeftSide>
            <ActionTitle>
              {translate(
                'pimee_catalog_rule.form.edit.actions.set_attribute.target_subtitle'
              )}
            </ActionTitle>
            <AttributeLocaleScopeSelector
              attributeId={`edit-rules-action-${lineNumber}-field`}
              attributeLabel={`${translate(
                'pimee_catalog_rule.form.edit.fields.attribute'
              )} ${translate('pim_common.required_label')}`}
              attributePlaceholder={translate(
                'pimee_catalog_rule.form.edit.actions.set_attribute.attribute_placeholder'
              )}
              attributeFormName={fieldFormName}
              attributeCode={action.field}
              scopeId={`edit-rules-action-${lineNumber}-scope`}
              scopes={scopes}
              localeId={`edit-rules-action-${lineNumber}-locale`}
              locales={locales}
              onAttributeChange={onAttributeChange}
              lineNumber={lineNumber}
              filterAttributeTypes={Object.keys(MANAGED_ATTRIBUTE_TYPES)}
              disabled={isUnmanagedAttribute() ? true : undefined}
            />
          </ActionLeftSide>
          <ActionRightSide>
            <ActionTitle>
              {translate(
                'pimee_catalog_rule.form.edit.actions.set_attribute.value_subtitle'
              )}
            </ActionTitle>
            <AttributeValue
              id={`edit-rules-action-${lineNumber}-value`}
              attribute={attribute}
              name={valueFormName}
              onChange={setValueFormValue}
              value={getValueFormValue()}
            />
          </ActionRightSide>
        </ActionGrid>
      </ActionTemplate>
    </>
  );
};

export { SetActionLine };
