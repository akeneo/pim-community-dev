import styled from 'styled-components';
import React from 'react';
import {SkeletonPlaceholder} from 'akeneo-design-system';

const QualityScoreLoader = () => {
  return (
    <Container data-testid="quality-score-loader">
      <Skeleton />
    </Container>
  );
};

const Container = styled.div`
  display: inline-flex;
  flex-flow: row nowrap;
  justify-content: flex-start;
  align-items: stretch;
  align-content: center;
  height: 25px;
  top: 1px;
  padding-top: 2px;
`;

const Skeleton = styled(SkeletonPlaceholder)`
  width: 102px;
  margin-right: 20px;
`;

export {QualityScoreLoader};
