import React from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { RemoveAttributeValueAction } from '../../../../models/actions';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import { Attribute } from '../../../../models';
import { useControlledFormInputAction } from '../../hooks';
import {
  useGetAttributeAtMount,
  validateAttribute,
} from './attribute/attribute.utils';
import {
  ActionGrid,
  ActionLeftSide,
  ActionRightSide,
  ActionTitle,
} from './ActionLine';
import {
  AttributeLocaleScopeSelector,
  AttributeValue,
  MANAGED_ATTRIBUTE_TYPES_FOR_REMOVE_ACTION,
} from './attribute';

type Props = {
  action: RemoveAttributeValueAction;
} & ActionLineProps;

const RemoveAttributeValueActionLine: React.FC<Props> = ({
  lineNumber,
  handleDelete,
  scopes,
  locales,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const [attribute, setAttribute] = React.useState<
    Attribute | null | undefined
  >(undefined);

  const {
    fieldFormName,
    typeFormName,
    itemsFormName,
    getItemsFormValue,
    setFieldFormValue,
    setItemsFormValue,
    getFieldFormValue,
  } = useControlledFormInputAction<string[]>(lineNumber);
  // Watch is needed in this case to trigger a render at input
  const { watch } = useFormContext();
  watch(itemsFormName);
  watch(fieldFormName);

  useGetAttributeAtMount(getFieldFormValue(), router, attribute, setAttribute);

  const onAttributeChange = (attribute: Attribute | null) => {
    setItemsFormValue([]);
    setAttribute(attribute);
    setFieldFormValue(attribute?.code);
  };

  return (
    <>
      <Controller
        name={fieldFormName}
        as={<span hidden />}
        defaultValue=''
        rules={{ validate: validateAttribute(translate, router) }}
      />
      <Controller
        name={typeFormName}
        as={<span hidden />}
        defaultValue='remove'
      />
      <Controller
        name={itemsFormName}
        as={<span hidden />}
        defaultValue={getItemsFormValue()}
        rules={{
          required: translate('pimee_catalog_rule.exceptions.required_value'),
        }}
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.remove_attribute_value.title'
        )}
        helper={translate(
          'pimee_catalog_rule.form.edit.actions.remove_attribute_value.helper'
        )}
        legend={translate(
          'pimee_catalog_rule.form.edit.actions.remove_attribute_value.helper'
        )}
        handleDelete={handleDelete}
        lineNumber={lineNumber}>
        <ActionGrid>
          <ActionLeftSide>
            <ActionTitle>
              {translate(
                'pimee_catalog_rule.form.edit.actions.set_attribute.target_subtitle'
              )}
            </ActionTitle>
            <AttributeLocaleScopeSelector
              attribute={attribute}
              attributeId={`edit-rules-action-${lineNumber}-field`}
              attributeLabel={`${translate(
                'pimee_catalog_rule.form.edit.fields.attribute'
              )} ${translate('pim_common.required_label')}`}
              attributePlaceholder={translate(
                'pimee_catalog_rule.form.edit.actions.set_attribute.attribute_placeholder'
              )}
              attributeFormName={fieldFormName}
              attributeCode={getFieldFormValue() ?? ''}
              scopeId={`edit-rules-action-${lineNumber}-scope`}
              scopes={scopes}
              localeId={`edit-rules-action-${lineNumber}-locale`}
              locales={locales}
              onAttributeCodeChange={onAttributeChange}
              lineNumber={lineNumber}
              filterAttributeTypes={Array.from(
                MANAGED_ATTRIBUTE_TYPES_FOR_REMOVE_ACTION.keys()
              )}
              disabled={
                !!attribute &&
                !MANAGED_ATTRIBUTE_TYPES_FOR_REMOVE_ACTION.has(attribute.type)
              }
            />
          </ActionLeftSide>
          <ActionRightSide>
            <ActionTitle>
              {translate(
                'pimee_catalog_rule.form.edit.actions.remove_attribute_value.value_subtitle'
              )}
            </ActionTitle>
            <AttributeValue
              id={`edit-rules-action-${lineNumber}-items`}
              attribute={attribute}
              name={itemsFormName}
              value={getItemsFormValue()}
              onChange={setItemsFormValue}
            />
          </ActionRightSide>
        </ActionGrid>
      </ActionTemplate>
    </>
  );
};

export { RemoveAttributeValueActionLine };
