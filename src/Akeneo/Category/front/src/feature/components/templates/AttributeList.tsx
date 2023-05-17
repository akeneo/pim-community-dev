import {Button, SectionTitle, Table, useBooleanState} from 'akeneo-design-system';
import {Attribute, CATEGORY_ATTRIBUTE_TYPE_AREA, CATEGORY_ATTRIBUTE_TYPE_RICHTEXT} from '../../models';
import {getLabelFromAttribute} from '../attributes';
import React, {useMemo} from 'react';
import {useFeatureFlags, userContext, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {AddTemplateAttributeModal} from './AddTemplateAttributeModal';

type Props = {
  attributes: Attribute[];
  selectedAttribute: Attribute;
  templateId: string;
  onAttributeSelection: (attribute: Attribute) => void;
};

export const AttributeList = ({attributes, selectedAttribute, templateId, onAttributeSelection}: Props) => {
  const translate = useTranslate();
  const catalogLocale = userContext.get('catalogLocale');
  const featureFlags = useFeatureFlags();

  const [isAddTemplateAttributeModalOpen, openAddTemplateAttributeModal, closeAddTemplateAttributeModal] =
    useBooleanState(false);

  const handleRowOnclick = (attribute: Attribute) => {
    onAttributeSelection(attribute);
  };

  const sortedAttributes = useMemo(
    () =>
      attributes.sort((attribute1: Attribute, attribute2: Attribute): number => {
        if (attribute1.order >= attribute2.order) {
          return 1;
        } else if (attribute1.order < attribute2.order) {
          return -1;
        }
        return 0;
      }),
    [attributes]
  );

  return (
    <AttributeListContainer>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.category.attributes')}</SectionTitle.Title>
        <AddAttributeButton ghost size="small" level="tertiary" onClick={openAddTemplateAttributeModal}>
          {translate('akeneo.category.template.add_attribute.add_button')}
        </AddAttributeButton>
      </SectionTitle>
      <ScrollablePanel>
        <Table>
          <Table.Header sticky={0}>
            <Table.HeaderCell>{translate('akeneo.category.template_list.columns.header')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('akeneo.category.template_list.columns.code')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('akeneo.category.template_list.columns.type')}</Table.HeaderCell>
          </Table.Header>
          <Table.Body>
            {sortedAttributes.map((attribute: Attribute) => (
              <Table.Row
                key={attribute.uuid}
                onClick={() => {
                  handleRowOnclick(attribute);
                }}
                isSelected={attribute === selectedAttribute}
              >
                <Table.Cell rowTitle>{getLabelFromAttribute(attribute, catalogLocale)}</Table.Cell>
                <Table.Cell>{attribute.code}</Table.Cell>
                <Table.Cell>
                  {translate(
                    `akeneo.category.template.attribute.type.${
                      attribute.type !== CATEGORY_ATTRIBUTE_TYPE_RICHTEXT
                        ? attribute.type
                        : CATEGORY_ATTRIBUTE_TYPE_AREA
                    }`
                  )}
                </Table.Cell>
              </Table.Row>
            ))}
          </Table.Body>
        </Table>
      </ScrollablePanel>
      {isAddTemplateAttributeModalOpen && (
        <AddTemplateAttributeModal templateId={templateId} onClose={closeAddTemplateAttributeModal} />
      )}
    </AttributeListContainer>
  );
};

const AttributeListContainer = styled.div`
  width: 100%;
`;

const AddAttributeButton = styled(Button)`
  margin-left: auto;
`;

const ScrollablePanel = styled.div`
  overflow-y: scroll;
  height: calc(100% - 44px);
`;
