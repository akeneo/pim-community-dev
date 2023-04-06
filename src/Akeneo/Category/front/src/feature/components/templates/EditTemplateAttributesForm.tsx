import React, {useState} from 'react';
import styled from 'styled-components';
import {Attribute} from '../../models';
import {AttributeList} from './AttributeList';
import {AttributeSettings} from './AttributeSettings';

interface Props {
  attributes: Attribute[];
  templateId: string;
}

export const EditTemplateAttributesForm = ({attributes, templateId}: Props) => {
  const [selectedAttribute, setSelectedAttribute] = useState<Attribute>(attributes[0]);
  const handleAttributeSelection = (attribute: Attribute) => {
    setSelectedAttribute(attribute);
  };

  return (
    <FormContainer>
      <Attributes>
        <AttributeList
          attributes={attributes}
          selectedAttribute={selectedAttribute}
          templateId={templateId}
          onAttributeSelection={handleAttributeSelection}
        ></AttributeList>
        {featureFlag.isEnabled('category_template_customization') &&
          <AttributeSettings attribute={selectedAttribute}></AttributeSettings>
        }
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
