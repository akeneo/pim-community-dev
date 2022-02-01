import styled from 'styled-components';
import React from 'react';
import {SkeletonPlaceholder} from 'akeneo-design-system';

const QualityScoreLoader = () => {
  return (
    <Container>
      <Skeleton />
      <Border />
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
  margin-right: 20px;
`;

const Border = styled.div`
  border-right: 1px ${({theme}) => theme.color.grey80} solid;
`;

const Skeleton = styled(SkeletonPlaceholder)`
  width: 102px;
  margin-right: 20px;
`;

export {QualityScoreLoader};
