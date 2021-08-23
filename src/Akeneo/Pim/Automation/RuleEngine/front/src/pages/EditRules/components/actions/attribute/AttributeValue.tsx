import React, {useContext} from 'react';
import {
  Attribute,
  AttributeType,
  getAttributeLabel,
} from '../../../../../models/Attribute';
import {ScopeCode} from '../../../../../models/Scope';
import {
  useTranslate,
  useUserCatalogLocale,
} from '../../../../../dependenciesTools/hooks';
import {FallbackValue} from './';
import {
  HelperContainer,
  InlineHelper,
} from '../../../../../components/HelpersInfos';
import {ActionFormContainer} from '../style';
import {
  AttributeValueConfig,
  ConfigContext,
} from '../../../../../context/ConfigContext';

const MANAGED_ATTRIBUTE_TYPES_FOR_SET_ACTION: AttributeType[] = [
  AttributeType.TEXT,
  AttributeType.OPTION_SIMPLE_SELECT,
  AttributeType.BOOLEAN,
  AttributeType.OPTION_MULTI_SELECT,
  AttributeType.NUMBER,
  AttributeType.PRICE_COLLECTION,
  AttributeType.DATE,
  AttributeType.ASSET_COLLECTION,
  AttributeType.REFERENCE_ENTITY_COLLECTION,
  AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
  AttributeType.TEXTAREA,
  AttributeType.METRIC,
];

const MANAGED_ATTRIBUTE_TYPES_FOR_REMOVE_ACTION: AttributeType[] = [
  AttributeType.OPTION_MULTI_SELECT,
  AttributeType.ASSET_COLLECTION,
  AttributeType.REFERENCE_ENTITY_COLLECTION,
  AttributeType.PRICE_COLLECTION,
];

const MANAGED_ATTRIBUTE_TYPES_FOR_ADD_ACTION: AttributeType[] = [
  AttributeType.OPTION_MULTI_SELECT,
  AttributeType.ASSET_COLLECTION,
  AttributeType.REFERENCE_ENTITY_COLLECTION,
  AttributeType.PRICE_COLLECTION,
];

type InputValueProps = {
  id: string;
  attribute: Attribute;
  name: string;
  validation?: {required?: string; validate?: (value: any) => string | true};
  value: any;
  label?: string;
  onChange: (value: any) => void;
  scopeCode?: ScopeCode;
};

const getValueModule = (
  attribute: Attribute,
  attributeValueConfig: AttributeValueConfig,
  props: InputValueProps,
  actionType?: string
) => {
  const render = attributeValueConfig[attribute.type]?.default;
  if (render) {
    return render(props, actionType);
  }

  return null;
};

type Props = {
  id: string;
  attribute?: Attribute | null;
  name: string;
  validation?: {required?: string; validate?: (value: any) => string | true};
  value?: any;
  label?: string;
  onChange: (value: any) => void;
  scopeCode?: ScopeCode;
  actionType: string;
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
  actionType,
}) => {
  const translate = useTranslate();
  const catalogLocale = useUserCatalogLocale();
  const {attributeValueConfig} = useContext(ConfigContext);

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
      return (
        getValueModule(
          attribute,
          attributeValueConfig,
          {
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
          },
          actionType
        ) ?? (
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
        )
      );
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
