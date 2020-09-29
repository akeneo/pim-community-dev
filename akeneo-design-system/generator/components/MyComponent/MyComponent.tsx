import React, {Ref} from 'react';
import styled from 'styled-components';

const MyComponentContainer = styled.span<{level: string}>``;

type MyComponentProps = {
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
const MyComponent = React.forwardRef<HTMLSpanElement, MyComponentProps>(
  ({level = 'primary', children, ...rest}: MyComponentProps, forwardedRef: Ref<HTMLSpanElement>) => {
    return (
      <MyComponentContainer level={level} ref={forwardedRef} {...rest}>
        {children}
      </MyComponentContainer>
    );
  }
);

export {MyComponent};
