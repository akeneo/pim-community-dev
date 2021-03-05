import React from 'react';
import styled from 'styled-components';
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

const QualityScoreProductModelHeader = () => {
  return (
    <Container>
      <Scoring bar />
      <Separator />
    </Container>
  );
};

export {QualityScoreProductModelHeader};
