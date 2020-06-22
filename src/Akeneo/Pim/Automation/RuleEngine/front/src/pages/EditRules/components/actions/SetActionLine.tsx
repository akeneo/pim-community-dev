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
import {
  AttributeLocaleScopeSelector,
  MANAGED_ATTRIBUTE_TYPES,
} from './attribute';
import { Attribute } from '../../../../models';
import { useRegisterConst } from '../../hooks/useRegisterConst';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { LineErrors } from '../LineErrors';
import { AttributeValue } from './attribute';

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

  useRegisterConst(`content.actions[${lineNumber}].type`, 'set');

  const onAttributeChange = (newAttribute: Attribute | null) => {
    setAttribute(newAttribute);
  };

  const isUnmanagedAttribute = () =>
    attribute && !(attribute.type in MANAGED_ATTRIBUTE_TYPES);

  return (
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
            name={`content.actions[${lineNumber}].value`}
            value={action.value}
          />
        </ActionRightSide>
      </ActionGrid>
    </ActionTemplate>
  );
};

export { SetActionLine };
