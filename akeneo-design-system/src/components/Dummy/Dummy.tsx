import React, {Ref} from 'react';
import styled from 'styled-components';

const DummyContainer = styled.span<{level: string}>``;

type DummyProps = {
  /**
   * TODO
   */
  level?: string;

  /**
   * TODO
   */
  children?: string;
};

/**
 * TODO
 */
const Dummy = React.forwardRef<HTMLSpanElement, DummyProps>(
  ({level = 'primary', children, ...rest}: DummyProps, forwardedRef: Ref<HTMLSpanElement>) => {
    return (
      <DummyContainer level={level} ref={forwardedRef} {...rest}>
        {children}
      </DummyContainer>
    );
  }
);

export {Dummy};
