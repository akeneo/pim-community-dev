import {useTranslate} from '@akeneo-pim-community/shared';
import {getColor, getFontSize, RulesIllustration} from 'akeneo-design-system';
import React from 'react';
import styled from 'styled-components';

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

const SourceDetailsPlaceholder = () => {
  const translate = useTranslate();

  return (
    <Content>
      <RulesIllustration size={128} />
      <Title>{translate('akeneo.tailored_export.column_details.sources.no_source_configuration.title')}</Title>
    </Content>
  );
};

export {SourceDetailsPlaceholder};
