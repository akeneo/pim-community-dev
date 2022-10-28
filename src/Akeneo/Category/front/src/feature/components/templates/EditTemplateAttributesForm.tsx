import React, {useContext, useEffect, useState} from 'react';
import styled from 'styled-components';
import {SectionTitle, Table} from 'akeneo-design-system';
import {LocaleSelector, useTranslate} from '@akeneo-pim-community/shared';
import {Attribute} from '../../models';
import {getLabelFromAttribute} from '../attributes/templateAttributesFactory';
import {EditCategoryContext} from '../providers';
import {isEqual} from 'lodash/fp';

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
  const [locale, setLocale] = useState('en_US');
  const {locales} = useContext(EditCategoryContext);
  const translate = useTranslate();

  const [selectedAttribute, setSelectedAttribute] = useState<Attribute>();

  useEffect(() => {
    if (!selectedAttribute && attributes && attributes.length > 0) {
      setSelectedAttribute(attributes[0]);
    }
  }, [attributes, selectedAttribute]);

  return (
    <FormContainer>
      <SectionTitle>
        <SectionTitle.Title>{translate('Attributes')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <LocaleSelector value={locale} values={Object.values(locales)} onChange={setLocale} />
      </SectionTitle>
      <Container>
        <TemplatesAttributeTable>
          <Table.Header>
            <Table.HeaderCell>{translate('akeneo.category.template_list.columns.header')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('akeneo.category.template_list.columns.code')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('akeneo.category.template_list.columns.type')}</Table.HeaderCell>
          </Table.Header>
          <Table.Body>
            {attributes?.map((attribute: Attribute) => (
              <Table.Row
                key={attribute.uuid}
                onClick={() => {
                  setSelectedAttribute(attribute);
                }}
                isSelected={isEqual(attribute, selectedAttribute)}
              >
                <Table.Cell rowTitle>{getLabelFromAttribute(attribute, locale)}</Table.Cell>
                <Table.Cell>{attribute.code}</Table.Cell>
                <Table.Cell>{attribute.type}</Table.Cell>
              </Table.Row>
            ))}
          </Table.Body>
        </TemplatesAttributeTable>
        <DescriptionPanel>
          <SectionTitle>
            <SectionTitle.Title>{translate('akeneo.category.template.attribute.description_title')}</SectionTitle.Title>
          </SectionTitle>
          {selectedAttribute && <h3>{getLabelFromAttribute(selectedAttribute, locale)}</h3>}
        </DescriptionPanel>
      </Container>
    </FormContainer>
  );
};

const Container = styled.div`
  display: flex;
  gap: 40px;
  padding-top: 10px;
`;
const TemplatesAttributeTable = styled(Table)`
  flex-basis: 60%;
  flex-grow: 1;
`;
const DescriptionPanel = styled.div`
  flex-basis: 40%;
`;
