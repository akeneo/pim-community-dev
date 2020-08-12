import React from 'react';
import styled from 'styled-components';

const DummyContainer = styled.div<{size: number}>`
  font-size: ${({size}) => size}px;
`;

type DummyProps = {
  size: number;
  onClick: () => void;
};

const Dummy = ({size, onClick}: DummyProps) => {
  return (
    <DummyContainer size={size} onClick={onClick}>
      Dummy
    </DummyContainer>
  );
};

export {Dummy};
