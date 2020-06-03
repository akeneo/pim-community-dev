import React from 'react';
import { SetAction } from '../../../../models/actions';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import {
  ActionGrid,
  ActionLeftSide,
  ActionRightSide,
  ActionTitle,
} from './ActionLine';
import { AttributeLocaleScopeSelector } from './AttributeLocaleScopeSelector';
import { ActionLineErrors } from './ActionLineErrors';
import { Attribute } from '../../../../models';
import { InputText } from '../../../../components/Inputs';
import { useFormContext } from 'react-hook-form';
import { FallbackField } from '../FallbackField';
import { useRegisterConsts } from "../../hooks/useRegisterConst";

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
  const [attribute, setAttribute] = React.useState<Attribute | null>(null);
  const { watch, setValue } = useFormContext();

  const values: any = {
    type: 'set',
    value: action.value,
    field: action.field,
  };
  if (action.locale) {
    values['locale'] = action.locale;
  }
  if (action.scope) {
    values['scope'] = action.scope;
  }
  const validateValue = {
    required: translate('pimee_catalog_rule.exceptions.required_value'),
  };

  useRegisterConsts(values, `content.actions[${lineNumber}]`);

  const displayNull = (value: any): string | null => {
    return null === value ? '' : null;
  };

  useValueInitialization(
    `content.actions[${lineNumber}]`,
    values,
    { value: validateValue },
    [action]
  );

  const getValueFormValue: () => any = () =>
    watch(`content.actions[${lineNumber}].value`);

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
            scopeCode={action.scope || null}
            scopes={scopes}
            localeId={`edit-rules-action-${lineNumber}-locale`}
            localeFormName={`content.actions[${lineNumber}].locale`}
            localeCode={action.locale || null}
            locales={locales}
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
              disabled
              name='value'
              label={`${translate('pimee_catalog_rule.rule.value')} ${translate(
                'pim_common.required_label'
              )} (under development)`}
              readOnly
              value={
                'string' === typeof getValueFormValue()
                  ? getValueFormValue()
                  : JSON.stringify(getValueFormValue())
              }
            />
          )}
        </ActionRightSide>
      </ActionGrid>
      <ActionLineErrors lineNumber={lineNumber} />
    </ActionTemplate>
  );
};

export { SetActionLine };
