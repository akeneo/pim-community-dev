import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const Container = styled.div`
  text-align: center;
  font-size: ${({theme}) => theme.fontSize.big};
  margin: 10px 0 0 0;
  width: 100%;
`;

const EmptyKeyIndicators = () => {
  const translate = useTranslate();

  return (
    <Container>
      <img src="bundles/akeneodataqualityinsights/images/empty-key-indicators.svg" />
      <p>{translate('akeneo_data_quality_insights.dqi_dashboard.key_indicators.no_data')}</p>
      <p>{translate('akeneo_data_quality_insights.dqi_dashboard.key_indicators.no_data_subtitle')}</p>
    </Container>
  );
};

export {EmptyKeyIndicators};
