import React, {Ref} from 'react';
import styled from 'styled-components';

//TODO be sure to select the appropriate container element here
const SquadDemoContainer = styled.span<{level: string}>``;

type SquadDemoProps = {
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
const SquadDemo = React.forwardRef<HTMLSpanElement, SquadDemoProps>(
  ({level = 'primary', children, ...rest}: SquadDemoProps, forwardedRef: Ref<HTMLSpanElement>) => {
    return (
      <SquadDemoContainer level={level} ref={forwardedRef} {...rest}>
        {children}
        <span>Hello Squad Review</span>
      </SquadDemoContainer>
    );
  }
);

export {SquadDemo};
