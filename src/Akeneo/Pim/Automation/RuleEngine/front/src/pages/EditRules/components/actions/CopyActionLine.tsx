import React from 'react';
import { CopyAction } from '../../../../models/actions';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useRegisterConst } from '../../hooks/useRegisterConst';
import { ActionGrid, ActionLeftSide, ActionRightSide, ActionTitle } from "./ActionLine";
import { useBackboneRouter, useTranslate } from "../../../../dependenciesTools/hooks";
import { AttributeLocaleScopeSelector } from "./attribute";
import { Attribute, AttributeType } from "../../../../models";
import { getAttributeByIdentifier } from "../../../../repositories/AttributeRepository";
import { useFormContext } from 'react-hook-form';
import { LineErrors } from "../LineErrors";

const supportedTypes: () => Map<AttributeType, AttributeType[]> = () => {
  return new Map([
    [AttributeType.OPTION_SIMPLE_SELECT, [
      AttributeType.OPTION_SIMPLE_SELECT,
      AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
      AttributeType.TEXT,
      AttributeType.TEXTAREA,
    ]],
    [AttributeType.OPTION_MULTI_SELECT, [
      AttributeType.OPTION_MULTI_SELECT,
      AttributeType.REFERENCE_ENTITY_COLLECTION,
      AttributeType.TEXT,
      AttributeType.TEXTAREA,
    ]],
    [AttributeType.TEXT, [
      AttributeType.TEXT,
      AttributeType.TEXTAREA,
      AttributeType.OPTION_SIMPLE_SELECT,
      AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
    ]],
    [AttributeType.IDENTIFIER, [
      AttributeType.IDENTIFIER,
      AttributeType.TEXT,
      AttributeType.TEXTAREA,
    ]],
    [AttributeType.DATE, [
      AttributeType.DATE,
      AttributeType.TEXT,
      AttributeType.TEXTAREA,
    ]],
    [AttributeType.METRIC, [
      AttributeType.METRIC,
      AttributeType.TEXT,
      AttributeType.TEXTAREA,
      AttributeType.NUMBER,
    ]],
    [AttributeType.NUMBER, [
      AttributeType.NUMBER,
      AttributeType.TEXT,
      AttributeType.TEXTAREA,
      AttributeType.METRIC,
    ]],
    [AttributeType.PRICE_COLLECTION, [
      AttributeType.PRICE_COLLECTION,
      AttributeType.TEXT,
      AttributeType.TEXTAREA,
    ]],
    [AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT, [
      AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
      AttributeType.TEXT,
      AttributeType.TEXTAREA,
      AttributeType.OPTION_SIMPLE_SELECT,
    ]],
    [AttributeType.REFERENCE_ENTITY_COLLECTION, [
      AttributeType.REFERENCE_ENTITY_COLLECTION,
      AttributeType.TEXT,
      AttributeType.TEXTAREA,
    ]],
  ]);
}

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
  const router = useBackboneRouter();
  const { watch, setValue } = useFormContext();
  useRegisterConst(`content.actions[${lineNumber}].type`, 'copy');

  const [ targetAttributeTypes, setTargetAttributeTypes ] = React.useState<AttributeType[]>([]);

  const handleSourceChange = (attribute: Attribute | null) => {
    const supported = attribute ? (supportedTypes().get(attribute.type) || []) : [];
    setTargetAttributeTypes(supported);
    const targetAttributeCode = watch(`content.actions[${lineNumber}].to_field`);
    if (targetAttributeCode) {
      getAttributeByIdentifier(targetAttributeCode, router).then((attribute) => {
        if (!attribute || !supported.includes(attribute.type)) {
          setValue(`content.actions[${lineNumber}].to_field`, null);
        }
      })
    }
  }

  React.useEffect(() => {
    if (action.from_field) {
      getAttributeByIdentifier(action.from_field, router).then((attribute) => {
        handleSourceChange(attribute);
      })
    }
  }, []);

  const sourceAttributeTypes = Array.from(supportedTypes().keys());

  return (
    <ActionTemplate
      title={translate('pimee_catalog_rule.form.edit.actions.copy.title')}
      helper={translate('pimee_catalog_rule.form.edit.actions.copy.helper')}
      legend={translate('pimee_catalog_rule.form.edit.actions.copy.helper')}
      handleDelete={handleDelete}>
      <LineErrors lineNumber={lineNumber} type='actions' />
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
            filterAttributeTypes={sourceAttributeTypes}
            onAttributeChange={handleSourceChange}
          />
        </ActionLeftSide>
        <ActionRightSide>
          <ActionTitle>
            {translate('pimee_catalog_rule.form.edit.actions.copy.select_target')}
          </ActionTitle>
          {targetAttributeTypes.length && (
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
              filterAttributeTypes={targetAttributeTypes}
            />
          )}
        </ActionRightSide>
      </ActionGrid>
    </ActionTemplate>
  );
};

export { CopyActionLine };
