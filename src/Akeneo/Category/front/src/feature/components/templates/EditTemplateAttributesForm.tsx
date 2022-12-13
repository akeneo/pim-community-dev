import React, {useCallback} from 'react';
import styled from 'styled-components';
import {SectionTitle, Table} from 'akeneo-design-system';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Attribute} from '../../models';
import {getLabelFromAttribute} from '../attributes/templateAttributesFactory';

interface Props {
  attributes: Attribute[];
}

const FormContainer = styled.div`
  margin-top: 20px;

  & > * {
    margin: 0 10px 20px 0;
  }
`;

export const EditTemplateAttributesForm = ({attributes}: Props) => {
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const translate = useTranslate();

  const sortByOrder = useCallback((attribute1: Attribute, attribute2: Attribute): number => {
    if (attribute1.order >= attribute2.order) {
      return 1;
    } else if (attribute1.order < attribute2.order) {
      return -1;
    }
    return 0;
  }, []);

  return (
    <FormContainer>
      <SectionTitle sticky={44}>
        <SectionTitle.Title>{translate('akeneo.category.attributes')}</SectionTitle.Title>
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
    </FormContainer>
  );
};
