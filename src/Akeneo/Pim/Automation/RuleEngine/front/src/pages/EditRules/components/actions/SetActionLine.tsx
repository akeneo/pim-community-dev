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

import { getAttributeByIdentifier } from '../../../../repositories/AttributeRepository';

type Props = {
  action?: SetAction;
} & ActionLineProps;

const SetActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  handleDelete,
  locales,
  scopes,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const [attribute, setAttribute] = React.useState<
    Attribute | null | undefined
  >(undefined);

  console.warn({ action });

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
  React.useEffect(() => {
    const fetchAttribute = async (attributeCode: AttributeCode) => {
      const attribute = await getAttributeByIdentifier(attributeCode, router);
      setAttribute(attribute);
    };
    const fieldValue = getFieldFormValue();
    if (fieldValue && !attribute) {
      fetchAttribute(fieldValue);
    }
  }, []);

  const onAttributeChange = (attributeCode: AttributeCode) => {
    const fetchAttribute = async (attributeCode: AttributeCode) => {
      const attribute = await getAttributeByIdentifier(attributeCode, router);
      setAttribute(attribute);
      setValueFormValue(null);
      setFieldFormValue(attributeCode);
    };
    fetchAttribute(attributeCode);
  };

  const isUnmanagedAttribute = () =>
    attribute && !(attribute.type in MANAGED_ATTRIBUTE_TYPES);

  if (getFieldFormValue() && !attribute) {
    return null;
  }

  const validateAttribute = async (value: any): Promise<string | true> => {
    if (!value) {
      return translate('pimee_catalog_rule.exceptions.required_attribute');
    }
    const attribute = await getAttributeByIdentifier(value, router);
    if (null === attribute) {
      return `${translate(
        'pimee_catalog_rule.exceptions.unknown_attribute'
      )} ${translate(
        'pimee_catalog_rule.exceptions.select_another_attribute_or_remove_action'
      )}`;
    }
    return true;
  };

  console.warn('getValueFormValue()', getValueFormValue());

  return (
    <>
      <Controller
        name={fieldFormName}
        as={<span hidden />}
        defaultValue=''
        rules={{ validate: validateAttribute }}
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
