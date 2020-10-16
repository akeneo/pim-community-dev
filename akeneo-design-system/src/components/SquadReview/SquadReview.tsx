import React, {Ref} from 'react';
import styled from 'styled-components';

//TODO be sure to select the appropriate container element here
const SquadReviewContainer = styled.span<{level: string}>``;

type SquadReviewProps = {
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
const SquadReview = React.forwardRef<HTMLSpanElement, SquadReviewProps>(
  ({level = 'primary', children, ...rest}: SquadReviewProps, forwardedRef: Ref<HTMLSpanElement>) => {
    return (
      <SquadReviewContainer level={level} ref={forwardedRef} {...rest}>
        {children}
        <span>Hello Squad review</span>
      </SquadReviewContainer>
    );
  }
);

export {SquadReview};
