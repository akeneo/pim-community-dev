import React, {ReactNode} from 'react';
import styled from 'styled-components';

const DummyContainer = styled.div<{size: number, type: Type}>`
  font-size: ${({size}) => size}px;
  line-height: ${({size}) => size + 5}px;
  color: ${({type}) => (type === 'primary' ? 'blue' : 'green')};
`;
export type Type = 'primary' | 'secondary';

type DummyProps = {
  /**
   * Defines the type of the Dummy component
   */
  type?: Type;
  /**
   * Defines the size of the Dummy component, in pixels
   */
  size?: number;
  /**
   * The handler called when clicking the component
   */
  onClick?: () => void;

  children?: ReactNode;
};

/**
 * This is a nice Dummy component to bootstrap Storybook
 */
const Dummy = ({size = 12, type = 'primary', onClick, children}: DummyProps) => {
  return (
    <DummyContainer size={size} onClick={onClick} type={type}>
      {children}
    </DummyContainer>
  );
};

export {Dummy};
export type {DummyProps};
