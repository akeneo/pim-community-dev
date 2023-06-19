import {useTranslate} from '@akeneo-pim-community/shared';
import {useState} from 'react';
import styled from 'styled-components';
import {Attribute} from '../../../models';
import {AttributeList} from './AttributeList';
import {AttributeSettings} from './AttributeSettings';
import {NoTemplateAttribute} from '../NoTemplateAttribute';

interface Props {
  attributes: Attribute[];
  templateId: string;
}

export const EditTemplateAttributesForm = ({attributes, templateId}: Props) => {
  const translate = useTranslate();

  const [selectedAttributeUuid, setSelectedAttributeUuid] = useState<string | null>(null);
  const handleAttributeSelection = (attribute: Attribute) => {
    setSelectedAttributeUuid(attribute.uuid);
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

  const selectedAttribute = attributes.find(attribute => attribute.uuid === selectedAttributeUuid) || attributes[0];

  return (
    <FormContainer>
      <Attributes>
        <AttributeList
          attributes={attributes}
          selectedAttribute={selectedAttribute}
          templateId={templateId}
          onAttributeSelection={handleAttributeSelection}
        />
        <AttributeSettings key={selectedAttribute.uuid} attribute={selectedAttribute} />
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
