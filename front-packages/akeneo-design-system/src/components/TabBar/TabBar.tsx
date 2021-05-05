import React, {HTMLAttributes, ReactNode} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';

const TabBarContainer = styled.div`
  display: flex;
  gap: 10px;
  margin: 0 40px 20px;
  height: 44px;
  border-bottom: 1px solid ${getColor('grey', 80)};
`;

type TabProps = {
  isActive: boolean;
};

const Tab = styled.div<TabProps & AkeneoThemedProps>`
  display: flex;
  align-items: center;
  gap: 10px;
  padding-right: 40px;
  color: ${({isActive}) => (isActive ? getColor('brand', 100) : getColor('grey', 100))};
  border-bottom: 3px solid ${({isActive}) => (isActive ? getColor('brand', 100) : 'transparent')};
  font-size: ${getFontSize('big')};
  cursor: pointer;

  &:hover {
    color: ${getColor('brand', 100)};
  }
`;

type TabBarProps = {
  /**
   * Tabs of the Tab bar.
   */
  children?: ReactNode;
} & HTMLAttributes<HTMLDivElement>;

/**
 * TabBar is used to move from one content to another within the same context.
 */
const TabBar = ({children, ...rest}: TabBarProps) => {
  return <TabBarContainer {...rest}>{children}</TabBarContainer>;
};

TabBar.Tab = Tab;

export {TabBar};
