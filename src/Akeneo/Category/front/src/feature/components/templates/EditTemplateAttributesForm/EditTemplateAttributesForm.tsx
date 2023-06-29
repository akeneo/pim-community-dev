import {useState} from 'react';
import styled from 'styled-components';
import {Attribute} from '../../../models';
import {InitializeTemplateChoice} from './InitializeTemplateChoice';
import {AttributeList} from './AttributeList';
import {AttributeSettings} from './AttributeSettings';

interface Props {
  attributes: Attribute[];
  templateId: string;
}

export const EditTemplateAttributesForm = ({attributes, templateId}: Props) => {
  const [selectedAttributeUuid, setSelectedAttributeUuid] = useState<string | null>(null);
  const handleAttributeSelection = (attribute: Attribute) => {
    setSelectedAttributeUuid(attribute.uuid);
  };

  if (attributes.length === 0) {
    return <InitializeTemplateChoice templateId={templateId} />;
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
