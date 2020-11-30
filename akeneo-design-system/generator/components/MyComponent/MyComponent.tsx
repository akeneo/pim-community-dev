import React, {Ref, ReactNode} from 'react';
import styled from 'styled-components';

//TODO be sure to select the appropriate container element here
const MyComponentContainer = styled.div<{level: string}>``;

type MyComponentProps = {
  /**
   * TODO.
   */
  level?: 'primary' | 'warning' | 'danger';

  /**
   * TODO.
   */
  children?: ReactNode;
};

/**
 * TODO.
 */
const MyComponent = React.forwardRef<HTMLDivElement, MyComponentProps>(
  ({level = 'primary', children, ...rest}: MyComponentProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <MyComponentContainer level={level} ref={forwardedRef} {...rest}>
        {children}
      </MyComponentContainer>
    );
  }
);

export {MyComponent};
