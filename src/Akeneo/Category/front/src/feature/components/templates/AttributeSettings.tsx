import {Button, Field, SectionTitle, TextInput, useBooleanState} from 'akeneo-design-system';
import {Attribute} from '../../models';
import React from 'react';
import {userContext, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {DeactivateTemplateAttributeModal} from './DeactivateTemplateAttributeModal';
import {getLabelFromAttribute} from '../attributes';

type Props = {
  attribute: Attribute;
  locales: string[];
};

export const AttributeSettings = ({attribute, locales}: Props) => {
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
        <SectionTitle sticky={0}>
            <SectionTitle.Title>
                {translate('akeneo.category.template.attribute.settings.options')}
            </SectionTitle.Title>
        </SectionTitle>
        <SectionTitle sticky={0}>
            <SectionTitle.Title>
                {translate('akeneo.category.template.attribute.settings.label_translations')}
            </SectionTitle.Title>
        </SectionTitle>
        <LabelTranslationsContainer>
            {locales !== null && locales.map((value, index): JSX.Element => (
                <TranslationField label={''} locale={value} key={index}>
                    <TextInput readOnly onChange={() => {}} value={attribute.labels[value] || ''}></TextInput>
                </TranslationField>
            ))}
        </LabelTranslationsContainer>
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

const LabelTranslationsContainer = styled.div`
`;

const TranslationField = styled(Field)`
  margin: 5px 0 5px 0;
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
