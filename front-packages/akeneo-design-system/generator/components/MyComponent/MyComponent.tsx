import React, {forwardRef, Ref, ReactNode, HTMLAttributes} from 'react';
import styled from 'styled-components';
import {Override} from '../../shared';

//TODO be sure to select the appropriate container element here
const MyComponentContainer = styled.div<{level: string}>``;

type MyComponentProps = Override<
  HTMLAttributes<HTMLDivElement>,
  {
    /**
     * TODO.
     */
    level?: 'primary' | 'warning' | 'danger';

    /**
     * TODO.
     */
    children?: ReactNode;
  }
>;

/**
 * TODO.
 */
const MyComponent = forwardRef<HTMLDivElement, MyComponentProps>(
  ({level = 'primary', children, ...rest}: MyComponentProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <MyComponentContainer level={level} ref={forwardedRef} {...rest}>
        {children}
      </MyComponentContainer>
    );
  }
);

export {MyComponent};
