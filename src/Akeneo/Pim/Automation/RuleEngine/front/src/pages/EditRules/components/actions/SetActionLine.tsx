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
import { Attribute, AttributeCode } from '../../../../models';
import {
  useTranslate,
  useBackboneRouter,
} from '../../../../dependenciesTools/hooks';
import { LineErrors } from '../LineErrors';
import { AttributeValue } from './attribute';
import { useControlledFormInputAction } from '../../hooks';
import {
  validateAttribute,
  useGetAttributeAtMount,
  fetchAttribute,
} from './attribute/attribute.utils';
import { SmallHelper } from '../../../../components/HelpersInfos';

type Props = {
  action?: SetAction;
} & ActionLineProps;

const SetActionLine: React.FC<Props> = ({
  lineNumber,
  handleDelete,
  locales,
  scopes,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
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
  } = useControlledFormInputAction<string>(lineNumber);
  // Watch is needed in this case to trigger a render at input
  const { watch } = useFormContext();
  watch(valueFormName);

  useGetAttributeAtMount(getFieldFormValue(), router, attribute, setAttribute);

  const onAttributeChange = (attributeCode: AttributeCode) => {
    const getAttribute = async (attributeCode: AttributeCode) => {
      const attribute = await fetchAttribute(router, attributeCode);
      setAttribute(attribute);
      setValueFormValue('');
      setFieldFormValue(attributeCode);
    };
    getAttribute(attributeCode);
  };

  const isUnmanagedAttribute = () =>
    attribute && !(attribute.type in MANAGED_ATTRIBUTE_TYPES);

  return (
    <>
      <Controller
        name={fieldFormName}
        as={<span hidden />}
        defaultValue=''
        rules={{ validate: validateAttribute(translate, router) }}
      />
      <Controller name={typeFormName} as={<span hidden />} defaultValue='set' />
      <Controller
        name={valueFormName}
        as={<span hidden />}
        defaultValue={getValueFormValue()}
        rules={{
          required: translate('pimee_catalog_rule.exceptions.required_value'),
        }}
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.set_attribute.title'
        )}
        helper={translate(
          'pimee_catalog_rule.form.edit.actions.set_attribute.helper'
        )}
        legend={translate(
          'pimee_catalog_rule.form.edit.actions.set_attribute.helper'
        )}
        handleDelete={handleDelete}>
        {attribute && !getValueFormValue() && (
          <SmallHelper level='info'>
            {translate(
              'pimee_catalog_rule.form.helper.set_attribute_info_clear'
            )}
          </SmallHelper>
        )}
        <LineErrors lineNumber={lineNumber} type='actions' />
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
              value={getValueFormValue()}
              onChange={setValueFormValue}
            />
          </ActionRightSide>
        </ActionGrid>
      </ActionTemplate>
    </>
  );
};

export { SetActionLine };
