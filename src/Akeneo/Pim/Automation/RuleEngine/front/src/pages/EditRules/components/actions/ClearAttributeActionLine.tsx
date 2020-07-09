import React from 'react';
import { Controller } from 'react-hook-form';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { ActionTitle } from './ActionLine';
import { ClearAttributeAction } from '../../../../models/actions';
import { AttributeLocaleScopeSelector } from './attribute/AttributeLocaleScopeSelector';
import { LineErrors } from '../LineErrors';
import {
  useTranslate,
  useBackboneRouter,
} from '../../../../dependenciesTools/hooks';
import { useControlledFormInputAction } from '../../hooks';
import { AttributeCode, Attribute } from '../../../../models';
import {
  validateAttribute,
  useGetAttributeAtMount,
  fetchAttribute,
} from './attribute/attribute.utils';

type Props = {
  action?: ClearAttributeAction;
} & ActionLineProps;

const ClearAttributeActionLine: React.FC<Props> = ({
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
    setFieldFormValue,
    getFieldFormValue,
  } = useControlledFormInputAction<string>(lineNumber);

  useGetAttributeAtMount(getFieldFormValue(), router, attribute, setAttribute);

  const onAttributeChange = (attributeCode: AttributeCode) => {
    const getAttribute = async (attributeCode: AttributeCode) => {
      const attribute = await fetchAttribute(router, attributeCode);
      setAttribute(attribute);
      setFieldFormValue(attributeCode);
    };
    getAttribute(attributeCode);
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
        defaultValue='clear'
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.clear_attribute.title'
        )}
        helper={translate('pimee_catalog_rule.form.helper.clear_attribute')}
        legend={translate('pimee_catalog_rule.form.helper.clear_attribute')}
        handleDelete={handleDelete}>
        <LineErrors lineNumber={lineNumber} type='actions' />
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
          onAttributeChange={onAttributeChange}
        />
      </ActionTemplate>
    </>
  );
};

export { ClearAttributeActionLine };
