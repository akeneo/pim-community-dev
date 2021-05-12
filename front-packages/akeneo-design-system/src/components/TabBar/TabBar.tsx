import React, {HTMLAttributes, ReactNode} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';

const TabBarContainer = styled.div`
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  height: 44px;
  border-bottom: 1px solid ${getColor('grey', 80)};
`;

type TabProps = {
  /**
   * Define if the tab is active.
   */
  isActive: boolean;
  /**
   * Function called when the user click on tab.
   */
  onClick?: () => void;
  /**
   * Content of the Tab.
   */
  children: ReactNode;
};

const TabContainer = styled.div<TabProps & AkeneoThemedProps>`
  display: flex;
  align-items: center;
  gap: 10px;
  padding-right: 40px;
  color: ${({isActive}) => (isActive ? getColor('brand', 100) : getColor('grey', 100))};
  border-bottom: 3px solid ${({isActive}) => (isActive ? getColor('brand', 100) : 'transparent')};
  font-size: ${getFontSize('big')};
  cursor: pointer;
  white-space: nowrap;

  &:hover {
    color: ${getColor('brand', 100)};
  }
`;

const Tab = ({children, isActive, ...rest}: TabProps) => {
  return (
    <TabContainer tabIndex={0} role="tab" aria-selected={isActive} isActive={isActive} {...rest}>
      {children}
    </TabContainer>
  );
};

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
  return (
    <TabBarContainer role="tablist" {...rest}>
      {children}
    </TabBarContainer>
  );
};

TabBar.Tab = Tab;

export {TabBar};
