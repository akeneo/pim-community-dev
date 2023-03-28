import React, {useCallback, useRef} from 'react';
import styled from 'styled-components';
import {AttributesIllustration, Button, SectionTitle, Table, useBooleanState} from 'akeneo-design-system';
import {
  NotificationLevel,
  useFeatureFlags,
  useNotify,
  useTranslate,
  useUserContext,
} from '@akeneo-pim-community/shared';
import {Attribute} from '../../models';
import {getLabelFromAttribute} from '../attributes';
import {AddTemplateAttributeModal} from './AddTemplateAttributeModal';
import {LabelContainer, PreviewCard, PreviewContainer} from "akeneo-design-system/lib/storybook";

interface Props {
  attributes: Attribute[];
  templateId: string;
}

const FormContainer = styled.div`
  margin-top: 20px;

  & > * {
    margin: 0 10px 20px 0;
  }
`;

const AddAttributeButton = styled(Button)`
  margin-left: auto;
`;

export const EditTemplateAttributesForm = ({attributes, templateId}: Props) => {
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const featureFlags = useFeatureFlags();
  const translate = useTranslate();
  const notify = useNotify();
  const attributesCountRef = useRef<Number>();

  attributesCountRef.current = attributes.length;

  const handleClickAddAttributeButton = useCallback(() => {
    if (attributesCountRef.current) {
      if (attributesCountRef.current >= 50) {
        notify(
            NotificationLevel.ERROR,
            translate('akeneo.category.template.add_attribute.error.limit_reached.title'),
            translate('akeneo.category.template.add_attribute.error.limit_reached.message')
        );
      } else {
        openAddTemplateAttributeModal();
      }
    }
  }, []);

  const sortByOrder = useCallback((attribute1: Attribute, attribute2: Attribute): number => {
    if (attribute1.order >= attribute2.order) {
      return 1;
    } else if (attribute1.order < attribute2.order) {
      return -1;
    }
    return 0;
  }, []);

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
      <Table>
        <Table.Header>
          <Table.HeaderCell>{translate('akeneo.category.template_list.columns.header')}</Table.HeaderCell>
          <Table.HeaderCell>{translate('akeneo.category.template_list.columns.code')}</Table.HeaderCell>
          <Table.HeaderCell>{translate('akeneo.category.template_list.columns.type')}</Table.HeaderCell>
        </Table.Header>
        <Table.Body>
          {attributes?.sort(sortByOrder).map((attribute: Attribute) => (
            <Table.Row key={attribute.uuid}>
              <Table.Cell rowTitle>{getLabelFromAttribute(attribute, catalogLocale)}</Table.Cell>
              <Table.Cell>{attribute.code}</Table.Cell>
              <Table.Cell>{translate(`akeneo.category.template.attribute.type.${attribute.type}`)}</Table.Cell>
            </Table.Row>
          ))}
        </Table.Body>
      </Table>
      {isAddTemplateAttributeModalOpen && (
        <AddTemplateAttributeModal templateId={templateId} onClose={closeAddTemplateAttributeModal} />
      )}
    </FormContainer>
  );
};
