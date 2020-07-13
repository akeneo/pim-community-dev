import React from 'react';
import { useGetAttributeAtMount } from './attribute/attribute.utils';
import { CopyAction } from '../../../../models/actions';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import {
  ActionGrid,
  ActionLeftSide,
  ActionRightSide,
  ActionTitle,
} from './ActionLine';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import { AttributeLocaleScopeSelector } from './attribute';
import { Attribute, AttributeType } from '../../../../models';
import { getAttributeByIdentifier } from '../../../../repositories/AttributeRepository';
import { useFormContext, Controller } from 'react-hook-form';
import { LineErrors } from '../LineErrors';
import { useControlledFormInputAction } from '../../hooks';
import styled from 'styled-components';

const EmptySourceHelper = styled.div`
  background-image: url('/bundles/pimui/images/icon-info.svg');
  background-repeat: no-repeat;
  padding-left: 28px;
  height: 21px;
  line-height: 21px;
`;

const supportedTypes: () => Map<AttributeType, AttributeType[]> = () => {
  return new Map([
    [
      AttributeType.OPTION_SIMPLE_SELECT,
      [
        AttributeType.OPTION_SIMPLE_SELECT,
        AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
        AttributeType.TEXT,
        AttributeType.TEXTAREA,
      ],
    ],
    [
      AttributeType.OPTION_MULTI_SELECT,
      [
        AttributeType.OPTION_MULTI_SELECT,
        AttributeType.REFERENCE_ENTITY_COLLECTION,
        AttributeType.TEXT,
        AttributeType.TEXTAREA,
      ],
    ],
    [
      AttributeType.TEXT,
      [
        AttributeType.TEXT,
        AttributeType.TEXTAREA,
        AttributeType.OPTION_SIMPLE_SELECT,
        AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
      ],
    ],
    [
      AttributeType.IDENTIFIER,
      [AttributeType.IDENTIFIER, AttributeType.TEXT, AttributeType.TEXTAREA],
    ],
    [
      AttributeType.DATE,
      [AttributeType.DATE, AttributeType.TEXT, AttributeType.TEXTAREA],
    ],
    [
      AttributeType.METRIC,
      [
        AttributeType.METRIC,
        AttributeType.TEXT,
        AttributeType.TEXTAREA,
        AttributeType.NUMBER,
      ],
    ],
    [
      AttributeType.NUMBER,
      [
        AttributeType.NUMBER,
        AttributeType.TEXT,
        AttributeType.TEXTAREA,
        AttributeType.METRIC,
      ],
    ],
    [
      AttributeType.PRICE_COLLECTION,
      [
        AttributeType.PRICE_COLLECTION,
        AttributeType.TEXT,
        AttributeType.TEXTAREA,
      ],
    ],
    [
      AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
      [
        AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
        AttributeType.TEXT,
        AttributeType.TEXTAREA,
        AttributeType.OPTION_SIMPLE_SELECT,
      ],
    ],
    [
      AttributeType.REFERENCE_ENTITY_COLLECTION,
      [
        AttributeType.REFERENCE_ENTITY_COLLECTION,
        AttributeType.TEXT,
        AttributeType.TEXTAREA,
      ],
    ],
  ]);
};

type Props = {
  action?: CopyAction;
} & ActionLineProps;

const CopyActionLine: React.FC<Props> = ({
  lineNumber,
  handleDelete,
  locales,
  scopes,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const { formName, typeFormName, getFormValue } = useControlledFormInputAction<
    string | null
  >(lineNumber);
  const { setValue, watch } = useFormContext();
  watch(formName('from_field'));
  watch(formName('to_field'));
  const [attributeSource, setAttributeSource] = React.useState<
    Attribute | null | undefined
  >(undefined);
  const [attributeTarget, setAttributeTarget] = React.useState<
    Attribute | null | undefined
  >(undefined);

  const [targetAttributeTypes, setTargetAttributeTypes] = React.useState<
    AttributeType[]
  >([]);

  const handleSourceChange = (attribute: Attribute | null) => {
    setValue(formName('from_field'), attribute?.code);
    setAttributeSource(attribute);
    const supported = attribute
      ? supportedTypes().get(attribute.type) || []
      : [];
    setTargetAttributeTypes(supported);
    const targetAttributeCode = getFormValue('to_field');
    if (targetAttributeCode) {
      getAttributeByIdentifier(targetAttributeCode, router).then(attribute => {
        setAttributeTarget(attribute);
        if (!attribute || !supported.includes(attribute.type)) {
          setValue(formName('to_field'), null);
        }
      });
    }
  };

  const handleTargetChange = (attribute: Attribute | null) => {
    setAttributeTarget(attribute);
    setValue(formName('to_field'), attribute?.code);
  };

  useGetAttributeAtMount(
    getFormValue('from_field'),
    router,
    attributeSource,
    (attribute: Attribute | null | undefined) => {
      if (attribute || attribute === null) {
        handleSourceChange(attribute);
      }
    }
  );

  const sourceAttributeTypes = Array.from(supportedTypes().keys());

  return (
    <>
      <Controller
        name={typeFormName}
        as={<span hidden />}
        defaultValue='copy'
      />
      <Controller
        name={formName('from_field')}
        as={<span hidden />}
        defaultValue={getFormValue('from_field')}
      />
      <Controller
        name={formName('to_field')}
        as={<span hidden />}
        defaultValue={getFormValue('to_field')}
      />
      <ActionTemplate
        title={translate('pimee_catalog_rule.form.edit.actions.copy.title')}
        helper={translate('pimee_catalog_rule.form.edit.actions.copy.helper')}
        legend={translate('pimee_catalog_rule.form.edit.actions.copy.helper')}
        handleDelete={handleDelete}>
        <LineErrors lineNumber={lineNumber} type='actions' />
        <ActionGrid>
          <ActionLeftSide>
            <ActionTitle>
              {translate(
                'pimee_catalog_rule.form.edit.actions.copy.select_source'
              )}
            </ActionTitle>
            <AttributeLocaleScopeSelector
              attribute={attributeSource}
              attributeCode={getFormValue('from_field')}
              attributeFormName={formName('from_field')}
              attributeId={`edit-rules-action-${lineNumber}-from-field`}
              attributeLabel={`${translate(
                'pimee_catalog_rule.form.edit.fields.attribute'
              )} ${translate('pim_common.required_label')}`}
              attributePlaceholder={translate(
                'pimee_catalog_rule.form.edit.actions.set_attribute.attribute_placeholder'
              )}
              scopeId={`edit-rules-action-${lineNumber}-from-scope`}
              localeId={`edit-rules-action-${lineNumber}-from-locale`}
              locales={locales}
              scopes={scopes}
              filterAttributeTypes={sourceAttributeTypes}
              onAttributeCodeChange={handleSourceChange}
              lineNumber={lineNumber}
              scopeFieldName={'from_scope'}
              localeFieldName={'from_locale'}
            />
          </ActionLeftSide>
          <ActionRightSide>
            <ActionTitle>
              {translate(
                'pimee_catalog_rule.form.edit.actions.copy.select_target'
              )}
            </ActionTitle>
            {targetAttributeTypes.length > 0 ? (
              <AttributeLocaleScopeSelector
                attribute={attributeTarget}
                attributeCode={getFormValue('to_field')}
                attributeFormName={formName('to_field')}
                attributeId={`edit-rules-action-${lineNumber}-to-field`}
                attributeLabel={`${translate(
                  'pimee_catalog_rule.form.edit.fields.attribute'
                )} ${translate('pim_common.required_label')}`}
                attributePlaceholder={translate(
                  'pimee_catalog_rule.form.edit.actions.set_attribute.attribute_placeholder'
                )}
                scopeId={`edit-rules-action-${lineNumber}-to-scope`}
                localeId={`edit-rules-action-${lineNumber}-to-locale`}
                locales={locales}
                scopes={scopes}
                filterAttributeTypes={targetAttributeTypes}
                lineNumber={lineNumber}
                onAttributeCodeChange={handleTargetChange}
                scopeFieldName={'to_scope'}
                localeFieldName={'to_locale'}
              />
            ) : (
              <EmptySourceHelper>
                {translate(
                  'pimee_catalog_rule.form.edit.actions.copy.no_source'
                )}
              </EmptySourceHelper>
            )}
          </ActionRightSide>
        </ActionGrid>
      </ActionTemplate>
    </>
  );
};

export { CopyActionLine };
