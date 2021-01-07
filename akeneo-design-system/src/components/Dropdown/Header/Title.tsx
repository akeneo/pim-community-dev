import styled from 'styled-components';
import {getColor} from '../../../theme';
import {Override} from '../../../shared';
import React, {ReactNode, Ref} from 'react';

const TitleContainer = styled.div`
  font-size: 11px;
  text-transform: uppercase;
  color: ${getColor('brand', 100)};
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
`;

type TitleProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    /**
     * The title of the dropdown
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
