import styled from 'styled-components';
import {getColor} from '../../../theme';
import {Override} from '../../../shared';
import React, {ReactNode, Ref} from 'react';

const HeaderContainer = styled.div`
  box-sizing: border-box;
  border-bottom: 1px solid ${getColor('brand', 100)};
  height: 34px;
  line-height: 34px;
  margin: 0 20px 10px 20px;
`;

type HeaderProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    /**
     * The content of the header.
     */
    children: ReactNode;
  }
>;

const Header = React.forwardRef<HTMLDivElement, HeaderProps>(
  ({children}: HeaderProps, forwardedRef: Ref<HTMLDivElement>): React.ReactElement => {
    return <HeaderContainer ref={forwardedRef}>{children}</HeaderContainer>;
  }
);

export {Header};
