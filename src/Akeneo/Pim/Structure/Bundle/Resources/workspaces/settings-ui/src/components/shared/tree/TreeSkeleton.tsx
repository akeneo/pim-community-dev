import React, {FC} from 'react';
import styled from 'styled-components';
import {Placeholder} from 'akeneo-design-system';

const Container = styled.div`
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
        <Placeholder key={i} />
      ))}
    </Container>
  );
};

export {TreeSkeleton};
