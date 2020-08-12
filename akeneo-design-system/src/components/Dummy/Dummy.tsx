import React from 'react';
import styled from 'styled-components';

const DummyContainer = styled.div<{size: number}>`
  font-size: ${({size}) => size}px;
`;

type DummyProps = {
  /**
   * Defines the size of the Dummy component, in pixels
   */
  size: number;
  /**
   * The handler called when clicking the component
   */
  onClick?: () => void;
};

/**
 * This is a nice Dummy component to bootstrap Storybook
 */
const Dummy = ({size = 12, onClick}: DummyProps) => {
  return (
    <DummyContainer size={size} onClick={onClick}>
      Dummy
    </DummyContainer>
  );
};

export {Dummy, DummyProps};
