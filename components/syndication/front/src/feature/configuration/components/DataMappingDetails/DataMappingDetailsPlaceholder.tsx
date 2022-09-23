import React from 'react';
import styled from 'styled-components';
import {
  getColor,
  getFontSize,
  RulesIllustration,
  Link,
  SectionTitle,
  Button,
  ArrowDownIcon,
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const Container = styled.div`
  height: 100%;
  overflow-y: auto;
  width: 500px;
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

const SubTitle = styled.div`
  color: ${getColor('grey', 100)};
  font-size: ${getFontSize('default')};
  text-align: center;
`;

const DataMappingDetailsPlaceholder = () => {
  const translate = useTranslate();

  return (
    <Container>
      <SectionTitle sticky={0}>
        <SectionTitle.Spacer />
        <Button size="small" ghost={true} level="tertiary" disabled>
          {translate('akeneo.syndication.data_mapping_details.sources.add')} <ArrowDownIcon />
        </Button>
      </SectionTitle>
      <Content>
        <RulesIllustration size={128} />
        <Title>{translate('akeneo.syndication.data_mapping_details.sources.no_data_mapping_selected.title')}</Title>
      </Content>
    </Container>
  );
};

const NoSourcePlaceholder = () => {
  const translate = useTranslate();

  return (
    <Content>
      <RulesIllustration size={128} />
      <Title>{translate('akeneo.syndication.data_mapping_details.sources.no_source_selected.title')}</Title>
      <SubTitle>
        <Link
          target="_blank"
          href="https://help.akeneo.com/pim/serenity/articles/syndication.html#define-your-export-structure"
        >
          {translate('akeneo.syndication.data_mapping_details.sources.no_source_selected.link')}
        </Link>
      </SubTitle>
    </Content>
  );
};

export {DataMappingDetailsPlaceholder, NoSourcePlaceholder};
