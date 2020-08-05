import React from 'react';
import { Attribute, getAttributeLabel, ScopeCode } from '../../../../../models';
import { AttributeType } from '../../../../../models/Attribute';
import {
  useTranslate,
  useUserCatalogLocale,
} from '../../../../../dependenciesTools/hooks';
import {
  AssetCollectionValue,
  BooleanValue,
  FallbackValue,
  MultiReferenceEntityValue,
  MultiSelectValue,
  NumberValue,
  parseMultiReferenceEntityValue,
  parsePriceCollectionValue,
  parseAssetCollectionValue,
  PriceCollectionValue,
  SimpleReferenceEntityValue,
  SimpleSelectValue,
  TextValue,
} from './';
import {
  HelperContainer,
  InlineHelper,
} from '../../../../../components/HelpersInfos';
import { ActionFormContainer } from '../style';

const MANAGED_ATTRIBUTE_TYPES_FOR_SET_ACTION: Map<
  AttributeType,
  React.FC<InputValueProps>
> = new Map([
  [AttributeType.TEXT, TextValue],
  [AttributeType.OPTION_SIMPLE_SELECT, SimpleSelectValue],
  [AttributeType.BOOLEAN, BooleanValue],
  [AttributeType.OPTION_MULTI_SELECT, MultiSelectValue],
  [AttributeType.NUMBER, NumberValue],
  [AttributeType.PRICE_COLLECTION, PriceCollectionValue],
  [AttributeType.ASSET_COLLECTION, AssetCollectionValue],
  [AttributeType.REFERENCE_ENTITY_COLLECTION, MultiReferenceEntityValue],
  [AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT, SimpleReferenceEntityValue],
]);

const MANAGED_ATTRIBUTE_TYPES_FOR_REMOVE_ACTION: Map<
  AttributeType,
  React.FC<InputValueProps>
> = new Map([
  [AttributeType.OPTION_MULTI_SELECT, MultiSelectValue],
  [AttributeType.ASSET_COLLECTION, AssetCollectionValue],
  [AttributeType.REFERENCE_ENTITY_COLLECTION, MultiReferenceEntityValue],
]);

const MANAGED_ATTRIBUTE_TYPES_FOR_ADD_ACTION: Map<
  AttributeType,
  React.FC<InputValueProps>
> = new Map([
  [AttributeType.OPTION_MULTI_SELECT, MultiSelectValue],
  [AttributeType.ASSET_COLLECTION, AssetCollectionValue],
  [AttributeType.REFERENCE_ENTITY_COLLECTION, MultiReferenceEntityValue],
]);

type InputValueProps = {
  id: string;
  attribute: Attribute;
  name: string;
  validation?: { required?: string; validate?: (value: any) => string | true };
  value: any;
  label?: string;
  onChange: (value: any) => void;
  scopeCode?: ScopeCode;
};

const getValueModule = (attribute: Attribute, props: InputValueProps) => {
  switch (attribute.type) {
    case AttributeType.TEXT:
      return <TextValue {...props} />;
    case AttributeType.OPTION_SIMPLE_SELECT:
      return <SimpleSelectValue {...props} key={attribute.code} />;
    case AttributeType.OPTION_MULTI_SELECT:
      return <MultiSelectValue {...props} key={attribute.code} />;
    case AttributeType.NUMBER:
      return <NumberValue {...props} />;
    case AttributeType.BOOLEAN:
      return <BooleanValue {...props} value={!!props.value} />;
    case AttributeType.PRICE_COLLECTION:
      return (
        <PriceCollectionValue
          {...props}
          value={parsePriceCollectionValue(props.value)}
        />
      );
    case AttributeType.ASSET_COLLECTION:
      return (
        <AssetCollectionValue
          {...props}
          value={parseAssetCollectionValue(props.value)}
        />
      );
    case AttributeType.REFERENCE_ENTITY_COLLECTION:
      return (
        <MultiReferenceEntityValue
          {...props}
          value={parseMultiReferenceEntityValue(props.value)}
        />
      );
    case AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT:
      return <SimpleReferenceEntityValue {...props} />;
    default:
      return null;
  }
};

type Props = {
  id: string;
  attribute?: Attribute | null;
  name: string;
  validation?: { required?: string; validate?: (value: any) => string | true };
  value?: any;
  label?: string;
  onChange: (value: any) => void;
  scopeCode?: ScopeCode;
};

const isAttrNotSelected = (attribute: Attribute | null | undefined) =>
  undefined === attribute;
const isAttrUnknown = (attribute: Attribute | null | undefined) =>
  null === attribute;

const AttributeValue: React.FC<Props> = ({
  id,
  attribute,
  name,
  validation = {},
  value,
  label,
  onChange,
  scopeCode,
}) => {
  const translate = useTranslate();
  const catalogLocale = useUserCatalogLocale();

  const getAttributeLabelIfNotNull = (
    attribute: Attribute | null | undefined
  ) => (attribute ? getAttributeLabel(attribute, catalogLocale) : undefined);

  /**
   * - if attribute is defined, it exists.
   * - if attribute is undefined, it is currently fetching
   * - if attribute is null, it does not exist.
   */
  const getAttributeValueContent = () => {
    if (isAttrNotSelected(attribute)) {
      return (
        <InlineHelper>
          {translate('pimee_catalog_rule.form.edit.please_select_attribute')}
        </InlineHelper>
      );
    }
    if (isAttrUnknown(attribute)) {
      return (
        <FallbackValue
          id={id}
          label={label || getAttributeLabelIfNotNull(attribute)}
          hiddenLabel={false}
          value={value}
        />
      );
    }
    if (attribute) {
      const inputComponent = getValueModule(attribute, {
        id,
        attribute,
        name,
        label: `${getAttributeLabel(attribute, catalogLocale)} ${translate(
          'pim_common.required_label'
        )}`,
        value,
        onChange,
        validation,
        scopeCode,
      });
      if (inputComponent) {
        return inputComponent;
      } else {
        return (
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
        );
      }
    }
    return null;
  };

  return (
    <ActionFormContainer>{getAttributeValueContent()}</ActionFormContainer>
  );
};

export {
  AttributeValue,
  InputValueProps,
  MANAGED_ATTRIBUTE_TYPES_FOR_SET_ACTION,
  MANAGED_ATTRIBUTE_TYPES_FOR_REMOVE_ACTION,
  MANAGED_ATTRIBUTE_TYPES_FOR_ADD_ACTION,
};
