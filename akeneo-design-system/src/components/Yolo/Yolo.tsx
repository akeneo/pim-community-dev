import React, {Ref} from 'react';
import styled from 'styled-components';

//TODO be sure to select the appropriate container element here
const YoloContainer = styled.span<{level: string}>``;

type YoloProps = {
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
const Yolo = React.forwardRef<HTMLSpanElement, YoloProps>(
  ({level = 'primary', children, ...rest}: YoloProps, forwardedRef: Ref<HTMLSpanElement>) => {
    return (
      <YoloContainer level={level} ref={forwardedRef} {...rest}>
        {children}
      </YoloContainer>
    );
  }
);

export {Yolo};
