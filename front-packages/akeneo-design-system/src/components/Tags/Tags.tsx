import React, {Ref, ReactNode} from 'react';
import styled from 'styled-components';

//TODO be sure to select the appropriate container element here
const TagsContainer = styled.div<{level: string}>``;

type TagsProps = {
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
const Tags = React.forwardRef<HTMLDivElement, TagsProps>(
  ({level = 'primary', children, ...rest}: TagsProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <TagsContainer level={level} ref={forwardedRef} {...rest}>
        {children}
      </TagsContainer>
    );
  }
);

export {Tags};
