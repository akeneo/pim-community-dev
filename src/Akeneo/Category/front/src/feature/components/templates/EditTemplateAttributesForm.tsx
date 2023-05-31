import React, {useState} from 'react';
import styled from 'styled-components';
import {Attribute} from '../../models';
import {AttributeList} from './AttributeList';
import {AttributeSettings} from './AttributeSettings';
import {useFeatureFlags, useTranslate} from '@akeneo-pim-community/shared';
import {NoTemplateAttribute} from './NoTemplateAttribute';
import {useCatalogActivatedLocales} from '../../hooks/useCatalogActivatedLocales';

interface Props {
  attributes: Attribute[];
  templateId: string;
  onTabStatusChange: (tabCode: 'attributes' | 'properties', inError: boolean) => void;
}

export const EditTemplateAttributesForm = ({attributes, templateId, onTabStatusChange}: Props) => {
  const featureFlag = useFeatureFlags();
  const translate = useTranslate();
  const locales = useCatalogActivatedLocales();
  const [selectedAttributeUuid, setSelectedAttributeUuid] = useState<string | null>(null);
  const [attributeFormsInError, setAttributeFormsInError] = useState<{string?: boolean}>({});
  const handleBadgesForFieldInError = (attributeUuid: string, inError: boolean) => {
    let updatedAttributeFormsInError = {...attributeFormsInError, [attributeUuid]: inError};
    let isAttributeTabInError = false;
    Object.values(updatedAttributeFormsInError).forEach(inError => {
      if (inError) {
        isAttributeTabInError = true;
      }
    });
    onTabStatusChange('attributes', isAttributeTabInError);
    setAttributeFormsInError(updatedAttributeFormsInError);
  };
  const handleAttributeSelection = (attribute: Attribute) => {
    setSelectedAttributeUuid(attribute.uuid);
  };
  const getSelectedAttribute = () => {
    return attributes.find(attribute => attribute.uuid === selectedAttributeUuid) ?? attributes[0];
  };

  if (attributes.length === 0) {
    return (
      <NoTemplateAttribute
        templateId={templateId}
        title={translate('akeneo.category.template.add_attribute.no_attribute_title')}
        instructions={translate('akeneo.category.template.add_attribute.no_attribute_instructions')}
        createButton={true}
      />
    );
  }
  return (
    <FormContainer>
      <Attributes>
        <AttributeList
          attributes={attributes}
          selectedAttribute={getSelectedAttribute()}
          templateId={templateId}
          onAttributeSelection={handleAttributeSelection}
          attributeFormsInError={attributeFormsInError}
        />
        {featureFlag.isEnabled('category_template_customization') && locales && (
          <AttributeSettings
            attribute={getSelectedAttribute()}
            activatedCatalogLocales={locales}
            onChangeFormStatus={handleBadgesForFieldInError}
          />
        )}
      </Attributes>
    </FormContainer>
  );
};

const FormContainer = styled.div`
  height: calc(100%);
  & > * {
    margin: 0 10px 20px 0;
  }
`;

const Attributes = styled.div`
  display: flex;
  width: 100%;
  height: calc(100% - 40px);
`;
