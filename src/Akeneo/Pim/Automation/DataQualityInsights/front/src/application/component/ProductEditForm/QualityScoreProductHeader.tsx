import React from 'react';
import styled from 'styled-components';
import {useCatalogContext, useFetchProductQualityScore} from '../../../infrastructure/hooks';
import {Scoring} from 'akeneo-design-system';

const Container = styled.div`
  display: flex;
  flex-direction: row;
`;

const Separator = styled.div`
  border-right: 1px ${({theme}) => theme.color.grey80} solid;
  width: 0;
  margin: 0 20px;
  height: 25px;
`;

const QualityScoreProductHeader = () => {
  const {channel, locale} = useCatalogContext();
  const score = useFetchProductQualityScore(channel, locale);

  return (
    <Container>
      <Scoring score={score ? score : null} bar />
      <Separator />
    </Container>
  );
};

export {QualityScoreProductHeader};
