import React, {useState} from 'react';
import styled from 'styled-components';
import {Attribute} from '../../models';
import {AttributeList} from './AttributeList';
import {AttributeSettings} from './AttributeSettings';
import {LabelCollection, useFeatureFlags, useTranslate} from '@akeneo-pim-community/shared';
import {NoTemplateAttribute} from './NoTemplateAttribute';
import {useCatalogActivatedLocales} from '../../hooks/useCatalogActivatedLocales';

interface Props {
  attributes: Attribute[];
  templateId: string;
}

type Translations = {[attributeUuid: string]: LabelCollection};

export const EditTemplateAttributesForm = ({attributes, templateId}: Props) => {
  const featureFlag = useFeatureFlags();
  const translate = useTranslate();
  const [selectedAttributeUuid, setSelectedAttributeUuid] = useState<string | null>(null);
  const locales = useCatalogActivatedLocales();
  const handleAttributeSelection = (attribute: Attribute) => {
    setSelectedAttributeUuid(attribute.uuid);
  };
  const getSelectedAttribute = () => {
    return attributes.find(attribute => attribute.uuid === selectedAttributeUuid) ?? attributes[0];
  };

  const [translations, setTranslations] = useState<Translations>({});
  // const [errors, setErrors] = useState<{[locale: string]: string[]}>({});

  const handleTranslationsChange = (locale: string, value: string) => {
    setTranslations({
      ...translations,
      [getSelectedAttribute().uuid]: {...translations[getSelectedAttribute().uuid], [locale]: value},
    });
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
        />
        {featureFlag.isEnabled('category_template_customization') && locales && (
          <AttributeSettings
            key={getSelectedAttribute().uuid}
            attribute={getSelectedAttribute()}
            activatedCatalogLocales={locales}
            translationsFormData={translations[getSelectedAttribute().uuid]}
            onTranslationsChange={handleTranslationsChange}
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
