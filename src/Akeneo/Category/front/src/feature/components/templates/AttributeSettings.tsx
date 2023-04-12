import {Button, SectionTitle, useBooleanState} from 'akeneo-design-system';
import {Attribute} from '../../models';
import React from 'react';
import {userContext, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {DeactivateTemplateAttributeModal} from './DeactivateTemplateAttributeModal';
import {getLabelFromAttribute} from '../attributes';

type Props = {
  attribute: Attribute;
};

export const AttributeSettings = ({attribute}: Props) => {
  const translate = useTranslate();
  const label = getLabelFromAttribute(attribute, userContext.get('catalogLocale'));
  const [
    isDeactivateTemplateAttributeModalOpen,
    openDeactivateTemplateAttributeModal,
    closeDeactivateTemplateAttributeModal,
  ] = useBooleanState(false);

  return (
    <SettingsContainer>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>
          {label} {translate('akeneo.category.template.attribute.settings.title')}
        </SectionTitle.Title>
      </SectionTitle>
      <Footer>
        <Button level="danger" ghost onClick={openDeactivateTemplateAttributeModal}>
          {translate('akeneo.category.template.attribute.delete_button')}
        </Button>
        {isDeactivateTemplateAttributeModalOpen && (
          <DeactivateTemplateAttributeModal
            templateUuid={attribute.template_uuid}
            attribute={{uuid: attribute.uuid, label: label}}
            onClose={closeDeactivateTemplateAttributeModal}
          />
        )}
      </Footer>
    </SettingsContainer>
  );
};

const SettingsContainer = styled.div`
  display: flex;
  flex-direction: column;
  padding-left: 40px;
  width: 510px;
  overflow-y: auto;
`;

const Footer = styled.div`
  display: flex;
  flex-direction: row-reverse;
  padding: 5px 0 5px;
  margin-top: 2px;
  position: sticky;
  bottom: 0;
  background-color: #ffffff;
`;
