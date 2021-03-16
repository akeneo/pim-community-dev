import React from 'react';
import styled from 'styled-components';
import {getColor} from 'akeneo-design-system';
import {usePimVersion} from '../hooks';

const PimVersion = () => {
  const pimVersion = usePimVersion();

  return (
    <Container>
      {pimVersion !== null && pimVersion.currentVersion}
    </Container>
  );
};

const Container = styled.div`
  text-align: center;
  color: ${getColor('grey', 100)};
  margin-top: 40px;
`;

export {PimVersion};
