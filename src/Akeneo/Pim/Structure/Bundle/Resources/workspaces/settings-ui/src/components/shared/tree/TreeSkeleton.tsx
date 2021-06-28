import styled from 'styled-components';
import {LoadingPlaceholderContainer} from '@akeneo-pim-community/shared';
import React, {FC} from 'react';

const Container = styled(LoadingPlaceholderContainer)`
  display: grid;
  grid-row-gap: 10px;

  > div {
    height: 54px;
  }
`;

const TreeSkeleton: FC = () => {
  return (
    <Container>
      {[...Array(5)].map((_e, i) => (
        <div key={i} />
      ))}
    </Container>
  );
};

export {TreeSkeleton};
