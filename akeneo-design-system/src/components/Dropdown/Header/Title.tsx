import styled from 'styled-components';
import {Override} from '../../../shared';
import React, {ReactNode, Ref} from 'react';
import {getSectionTitleStyle} from 'typography';

const TitleContainer = styled.div`
  ${getSectionTitleStyle({
    size: 'small',
    color: 'brand',
  })}

  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
`;

type TitleProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    /**
     * The title of the dropdown.
     */
    children: ReactNode;
  }
>;

const Title = React.forwardRef<HTMLDivElement, TitleProps>(
  ({children}: TitleProps, forwardedRef: Ref<HTMLDivElement>): React.ReactElement => {
    return <TitleContainer ref={forwardedRef}>{children}</TitleContainer>;
  }
);

export {Title};
