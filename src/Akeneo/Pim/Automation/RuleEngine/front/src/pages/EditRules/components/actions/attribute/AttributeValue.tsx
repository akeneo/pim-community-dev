import React from 'react';
import { Attribute, getAttributeLabel } from '../../../../../models';
import {
  useTranslate,
  useUserCatalogLocale,
} from '../../../../../dependenciesTools/hooks';
import { TextValue } from './TextValue';
import { FallbackValue } from './FallbackValue';
import { SimpleSelectValue } from './SimpleSelectValue';
import { InlineHelper } from '../../../../../components/HelpersInfos';
import { ActionFormContainer } from '../style';
import styled from 'styled-components';

const HelperContainer = styled.div`
  margin-top: 15px;
`;

const MANAGED_ATTRIBUTE_TYPES: { [key: string]: React.FC<InputValueProps> } = {
  pim_catalog_text: TextValue,
  pim_catalog_simpleselect: SimpleSelectValue,
};

type InputValueProps = {
  id: string;
  attribute: Attribute;
  name: string;
  validation?: { required?: string; validate?: (value: any) => string | true };
  value: any;
  label?: string;
  onChange: (value: any) => void;
};

const getValueModule = (attribute: Attribute, props: InputValueProps) => {
  switch (attribute.type) {
    case 'pim_catalog_text':
      return <TextValue {...props} />;
    case 'pim_catalog_simpleselect':
      return <SimpleSelectValue {...props} key={attribute.code} />;
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
}) => {
  const catalogLocale = useUserCatalogLocale();

  const translate = useTranslate();
  const getAttributeValueContent = () => {
    if (isAttrNotSelected(attribute)) {
      return (
        <InlineHelper>
          {translate('pimee_catalog_rule.form.edit.please_select_attribute')}
        </InlineHelper>
      );
    }
    if (isAttrUnknown(attribute)) {
      return <FallbackValue id={id} label={label} value={value} />;
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
      });
      if (inputComponent) {
        return inputComponent;
      } else {
        return (
          <FallbackValue id={id} label={label} value={value}>
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

export { AttributeValue, InputValueProps, MANAGED_ATTRIBUTE_TYPES };
