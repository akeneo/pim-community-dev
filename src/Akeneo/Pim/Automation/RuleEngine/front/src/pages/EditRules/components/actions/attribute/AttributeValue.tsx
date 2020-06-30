import React from 'react';
import { Attribute, getAttributeLabel, AttributeType } from '../../../../../models';
import {
  useTranslate,
  useUserCatalogLocale,
} from '../../../../../dependenciesTools/hooks';
import { TextValue } from './TextValue';
import { FallbackValue } from './FallbackValue';
import { SimpleSelectValue } from './SimpleSelectValue';
import {
  HelperContainer,
  InlineHelper,
} from '../../../../../components/HelpersInfos';
import { ActionFormContainer } from '../style';

const MANAGED_ATTRIBUTE_TYPES: Map<AttributeType, React.FC<InputValueProps>> = new Map([
  [AttributeType.TEXT, TextValue],
  [AttributeType.OPTION_SIMPLE_SELECT, SimpleSelectValue],
]);

type InputValueProps = {
  id: string;
  attribute: Attribute;
  name: string;
  validation?: { required?: string; validate?: (value: any) => string | true };
  value: any;
  label?: string;
  onChange: (value: any) => void;
};

const getValueModule: (
  attribute: Attribute
) => React.FC<InputValueProps> | null = attribute => {
  return MANAGED_ATTRIBUTE_TYPES.get(attribute.type) || null;
};

enum AttributeStatus {
  NOT_SELECTED,
  UNKNOWN,
  UNMANAGED,
  VALID,
}

type Props = {
  id: string;
  attribute?: Attribute | null;
  name: string;
  validation?: { required?: string; validate?: (value: any) => string | true };
  value: any;
  label?: string;
  onChange: (value: any) => void;
};

const AttributeValue: React.FC<Props> = ({
  id,
  attribute,
  name,
  validation = {},
  value,
  label,
  onChange,
}) => {
  const translate = useTranslate();
  const catalogLocale = useUserCatalogLocale();

  const [ValueModule, setValueModule] = React.useState<React.FC<
    InputValueProps
  > | null>(null);
  React.useEffect(() => {
    setValueModule(() => (attribute ? getValueModule(attribute) : null));
  }, [attribute]);

  const getAttributeStatus = (): AttributeStatus => {
    if (undefined === attribute) {
      return AttributeStatus.NOT_SELECTED;
    }

    if (null === attribute) {
      return AttributeStatus.UNKNOWN;
    }

    if (attribute && ValueModule) {
      return AttributeStatus.VALID;
    }

    return AttributeStatus.UNMANAGED;
  };

  const getAttributeLabelIfNotNull = (
    attribute: Attribute | null | undefined
  ) => (attribute ? getAttributeLabel(attribute, catalogLocale) : undefined);

  return (
    <ActionFormContainer>
      {getAttributeStatus() === AttributeStatus.NOT_SELECTED && (
        <InlineHelper>
          {translate('pimee_catalog_rule.form.edit.please_select_attribute')}
        </InlineHelper>
      )}
      {getAttributeStatus() === AttributeStatus.UNKNOWN && (
        <FallbackValue
          id={id}
          label={label || getAttributeLabelIfNotNull(attribute)}
          hiddenLabel={false}
          value={value}
        />
      )}
      {getAttributeStatus() === AttributeStatus.UNMANAGED && (
        <FallbackValue
          id={id}
          label={label || getAttributeLabelIfNotNull(attribute)}
          hiddenLabel={false}
          value={value}>
          <HelperContainer>
            <InlineHelper>
              {translate(
                'pimee_catalog_rule.form.edit.unhandled_attribute_type'
              )}
            </InlineHelper>
          </HelperContainer>
        </FallbackValue>
      )}
      {/* The key attribute here is used to force react to make an mount / unmount at each attribute change */}
      {getAttributeStatus() === AttributeStatus.VALID &&
        ValueModule &&
        attribute && (
          <ValueModule
            key={attribute.code}
            id={id}
            attribute={attribute}
            name={name}
            label={label}
            value={value}
            onChange={onChange}
            validation={validation}
          />
        )}
    </ActionFormContainer>
  );
};

export { AttributeValue, InputValueProps, MANAGED_ATTRIBUTE_TYPES };
