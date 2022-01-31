import styled from 'styled-components';
import React from 'react';
import {getColor, LoaderIcon} from 'akeneo-design-system';

const QualityScoreLoader = () => {
  return (
    <Container>
      <QualityScoreLoaderIcon />
    </Container>
  );
};

const QualityScoreLoaderIcon = styled(LoaderIcon)`
  color: ${getColor('grey100')};
`;

const Container = styled.div`
  display: flex;
  position: relative;
  top: 1px;
  border-right: 1px ${({theme}) => theme.color.grey80} solid;
  padding-right: 20px;
  margin-right: 20px;
  padding-top: 2px;
  height: 25px;
`;

export {QualityScoreLoader};
