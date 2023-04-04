import React, {useState} from 'react';
import styled from 'styled-components';
import {Button, SectionTitle, useBooleanState} from 'akeneo-design-system';
import {
  NotificationLevel,
  useFeatureFlags,
  useNotify,
  useTranslate,
} from '@akeneo-pim-community/shared';
import {Attribute} from '../../models';
import {AddTemplateAttributeModal} from './AddTemplateAttributeModal';
import {AttributeList} from "./AttributeList";
import {AttributeSettings} from "./AttributeSettings";

interface Props {
  attributes: Attribute[];
  templateId: string;
}

export const EditTemplateAttributesForm = ({attributes, templateId}: Props) => {
  const featureFlags = useFeatureFlags();
  const translate = useTranslate();
  const notify = useNotify();
  const [selectedAttribute, setSelectedAttribute] = useState<Attribute>(attributes[0]);

  const handleClickAddAttributeButton = () => {
    if (attributes.length >= 50) {
      notify(
          NotificationLevel.ERROR,
          translate('akeneo.category.template.add_attribute.error.limit_reached.title'),
          translate('akeneo.category.template.add_attribute.error.limit_reached.message')
      );
    } else {
      openAddTemplateAttributeModal();
    }
  };
  const handleAttributeSelection = (attribute: Attribute) => {
    setSelectedAttribute(attribute);
  };

  const [isAddTemplateAttributeModalOpen, openAddTemplateAttributeModal, closeAddTemplateAttributeModal] =
    useBooleanState(false);

  return (
    <FormContainer>
      <SectionTitle sticky={44}>
        <SectionTitle.Title>{translate('akeneo.category.attributes')}</SectionTitle.Title>
        {featureFlags.isEnabled('category_template_customization') && (
          <AddAttributeButton ghost size="small" level="tertiary" onClick={handleClickAddAttributeButton}>
            {translate('akeneo.category.template.add_attribute.add_button')}
          </AddAttributeButton>
        )}
      </SectionTitle>
      <Attributes>
        <AttributeList attributes={attributes} templateId={templateId} onAttributeSelection={handleAttributeSelection}></AttributeList>
        <AttributeSettings attribute={selectedAttribute}></AttributeSettings>
      </Attributes>
      {isAddTemplateAttributeModalOpen && (
        <AddTemplateAttributeModal templateId={templateId} onClose={closeAddTemplateAttributeModal} />
      )}
    </FormContainer>
  );
};

const FormContainer = styled.div`
  margin-top: 20px;

  & > * {
    margin: 0 10px 20px 0;
  }
`;

const AddAttributeButton = styled(Button)`
  margin-left: auto;
`;

const Attributes = styled.div`
  display: flex;
  flex-direction: row;
`;
