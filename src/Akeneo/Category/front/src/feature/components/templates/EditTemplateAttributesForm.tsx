import React from 'react';
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

  return (
    <FormContainer>
      <SectionTitle>
        <SectionTitle.Title>{translate('Attributes')}</SectionTitle.Title>
      </SectionTitle>
      <Table>
        <Table.Header>
          <Table.HeaderCell>{translate('akeneo.category.template_list.columns.header')}</Table.HeaderCell>
          <Table.HeaderCell>{translate('akeneo.category.template_list.columns.code')}</Table.HeaderCell>
          <Table.HeaderCell>{translate('akeneo.category.template_list.columns.type')}</Table.HeaderCell>
        </Table.Header>
        <Table.Body>
          {attributes?.map((attribute: Attribute) => (
            <Table.Row
              key={attribute.uuid}
            >
              <Table.Cell rowTitle>{getLabelFromAttribute(attribute, catalogLocale)}</Table.Cell>
              <Table.Cell>{attribute.code}</Table.Cell>
              <Table.Cell>{attribute.type}</Table.Cell>
            </Table.Row>
          ))}
        </Table.Body>
      </Table>
    </FormContainer>
  );
};
