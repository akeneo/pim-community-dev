import React from 'react';
import styled from 'styled-components';
import {
  getColor,
  getFontSize,
  RulesIllustration,
  SectionTitle,
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const Container = styled.div`
  height: 100%;
  overflow-y: auto;
  flex: 1;
  display: flex;
  flex-direction: column;
`;

const Content = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 60px 0;
  padding: 20px;
  gap: 10px;
`;

const Title = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('big')};
  text-align: center;
`;

const DataMappingDetailsPlaceholder = () => {
  const translate = useTranslate();

  return (
    <Container>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_import.data_mapping.title')}</SectionTitle.Title>
      </SectionTitle>
      <Content>
        <RulesIllustration size={128} />
        <Title>{translate('akeneo.tailored_import.data_mapping.no_data_mapping_selected')}</Title>
      </Content>
    </Container>
  );
};

export {DataMappingDetailsPlaceholder};
