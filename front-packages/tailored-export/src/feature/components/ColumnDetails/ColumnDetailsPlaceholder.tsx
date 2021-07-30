import React from 'react';
import styled from 'styled-components';
import {getColor, getFontSize, RulesIllustration, Link, SectionTitle, Button} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const Container = styled.div`
  height: 100%;
  overflow-y: auto;
  width: 400px;
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

const ColumnDetailsPlaceholder = () => {
  const translate = useTranslate();

  return (
    <Container>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_export.column_details.sources.title')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <Button size="small" ghost={true} level="tertiary" disabled>
          {translate('akeneo.tailored_export.column_details.sources.add')}
        </Button>
      </SectionTitle>
      <Content>
        <RulesIllustration size={128} />
        <Title>{translate('akeneo.tailored_export.column_details.sources.no_column_selected.title')}</Title>
      </Content>
    </Container>
  );
};

const NoSourcePlaceholder = () => {
  const translate = useTranslate();

  return (
    <Content>
      <RulesIllustration size={128} />
      <Title>{translate('akeneo.tailored_export.column_details.sources.no_source_selected.title')}</Title>
      <SubTitle>
        <Link href="https://help.akeneo.com/pim/serenity/articles/tailored-export.html#define-your-export-structure">
          {translate('akeneo.tailored_export.column_details.sources.no_source_selected.link')}
        </Link>
      </SubTitle>
    </Content>
  );
};
export {ColumnDetailsPlaceholder, NoSourcePlaceholder};
