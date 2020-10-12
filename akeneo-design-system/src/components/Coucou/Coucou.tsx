import React, {Ref} from 'react';
import styled from 'styled-components';

//TODO be sure to select the appropriate container element here
const CoucouContainer = styled.span<{level: string}>`
  color: red;
`;

type CoucouProps = {
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
const Coucou = React.forwardRef<HTMLSpanElement, CoucouProps>(
  ({level = 'primary', children, ...rest}: CoucouProps, forwardedRef: Ref<HTMLSpanElement>) => {
    return (
      <CoucouContainer level={level} ref={forwardedRef} {...rest}>
        {children}
        <span>ddd</span>
      </CoucouContainer>
    );
  }
);

export {Coucou};
