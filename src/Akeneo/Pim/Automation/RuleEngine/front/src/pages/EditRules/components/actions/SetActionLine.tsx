import React from 'react';
import { SetAction } from '../../../../models/actions';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import {
  ActionGrid,
  ActionLeftSide,
  ActionRightSide,
  ActionTitle,
} from './ActionLine';
import { AttributeLocaleScopeSelector } from './AttributeLocaleScopeSelector';
import { Attribute } from '../../../../models';
import { InputText } from '../../../../components/Inputs';
import { useFormContext } from 'react-hook-form';
import { useRegisterConst } from '../../hooks/useRegisterConst';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { LineErrors } from "../LineErrors";

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
  const [attribute, setAttribute] = React.useState<Attribute | null>(null);
  const { register, setValue } = useFormContext();

  const validateValue = {
    required: translate('pimee_catalog_rule.exceptions.required_value'),
  };

  useRegisterConst('type', 'set', `content.actions[${lineNumber}]`);

  const setValueFormValue = (value: any) => {
    setValue(`content.actions[${lineNumber}].value`, value);
  };

  const onAttributeChange = (newAttribute: Attribute | null) => {
    const oldAttributeCode = attribute?.code;
    const newAttributeCode = newAttribute?.code;
    setAttribute(newAttribute);
    if (oldAttributeCode && oldAttributeCode !== newAttributeCode) {
      setValueFormValue(null);
    }
  };

  return (
    <ActionTemplate
      title='Set Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
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
            attributeFormName={`content.actions[${lineNumber}].field`}
            attributeCode={action.field}
            scopeId={`edit-rules-action-${lineNumber}-scope`}
            scopeFormName={`content.actions[${lineNumber}].scope`}
            scopes={scopes}
            scopeValue={action.scope || undefined}
            localeId={`edit-rules-action-${lineNumber}-locale`}
            localeFormName={`content.actions[${lineNumber}].locale`}
            locales={locales}
            localeValue={action.locale || undefined}
            onAttributeChange={onAttributeChange}
          />
        </ActionLeftSide>
        <ActionRightSide>
          <ActionTitle>
            {translate(
              'pimee_catalog_rule.form.edit.actions.set_attribute.value_subtitle'
            )}
          </ActionTitle>
          {null === attribute && (
            <div>
              {translate(
                'pimee_catalog_rule.form.edit.actions.set_attribute.unknown_attribute'
              )}
            </div>
          )}
          {null !== attribute && (
            <InputText
              data-testid={`edit-rules-input-${lineNumber}-value`}
              name={`content.conditions[${lineNumber}].value`}
              label={`${translate('pimee_catalog_rule.rule.value')} ${translate(
                'pim_common.required_label'
              )}`}
              ref={register(validateValue)}
              hiddenLabel={true}
              defaultValue={action.value}
              disabled
              readOnly
            />
          )}
        </ActionRightSide>
      </ActionGrid>
      <LineErrors lineNumber={lineNumber} type='actions' />
    </ActionTemplate>
  );
};

export { SetActionLine };
