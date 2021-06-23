import React, {FC, useState} from 'react';
import styled from 'styled-components';
import {PimView, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {IconProps, MainNavigationItem, SubNavigationPanel} from 'akeneo-design-system';

const NavContainer = styled.nav`
  display: flex;
  height: 100%;
`;

const MainNavContainer = styled.div`
  display: flex;
  width: 80px;
  flex-direction: column;
  justify-content: start;
  height: 100%;
  border-right: 1px solid ${({theme}) => theme.color.grey80};
  z-index: 803;
  background: white;
`;
const SubNavContainer = styled.div``;

const LogoContainer = styled.div`
  height: 80px;
  min-height: 80px;
  position: relative;
`;
const MenuContainer = styled.div``;
const HelpContainer = styled.div`
  height: 80px;
  min-height: 80px;
  position: relative;
  margin-top: auto;
`;

type SubNavigationEntry = {
  position: number;
  route: string;
  target: string;
  label: string;
  section?: any;
};

type NavigationEntry = {
  code: string;
  label: string;
  active: boolean;
  disabled?: boolean;
  route: string;
  icon: React.ReactElement<IconProps>;
  position: number;
  items: SubNavigationEntry[];
  sections: any[];
  isLandingSectionPage: boolean;
};

type Props = {
  entries: NavigationEntry[];
};
const PimNavigation: FC<Props> = ({entries}) => {
  const translate = useTranslate();
  const router = useRouter();

  const handleFollowEntry = (entry: NavigationEntry) => {
    setActiveEntry(entry);
    router.redirect(router.generate(entry.route));
  };

  // @todo initial state: set the initial state with the active entry or the first of the list
  const [activeEntry, setActiveEntry] = useState<NavigationEntry | null>(entries[0] || null);
  const isActiveEntry = (code: string) => code === (activeEntry ? activeEntry.code : null);

  // @todo read default value by main navigation entry from Session storage
  // @example: collapsedColumn_pim-menu-settings: 1
  const isSubNavigationOpened = true;

  const getEntrySubNavigation = () => {
    if (!activeEntry) {
      return [];
    }
    return activeEntry.items;
  };

  console.log('entries', entries);

  const subNavigationItems = getEntrySubNavigation();

  return (
    <NavContainer aria-label="Main Navigation">
      <MainNavContainer>
        <LogoContainer>
          <PimView viewName="pim-menu-logo" />
        </LogoContainer>
        <MenuContainer>
          {entries.map(entry => (
            <MainNavigationItem
              key={entry.code}
              active={isActiveEntry(entry.code)}
              disabled={entry.disabled}
              icon={entry.icon}
              onClick={() => handleFollowEntry(entry)}
            >
              {translate(entry.label)}
            </MainNavigationItem>
          ))}
        </MenuContainer>
        <HelpContainer>
          <PimView viewName="pim-menu-help" />
        </HelpContainer>
      </MainNavContainer>
      {activeEntry && !activeEntry.isLandingSectionPage && subNavigationItems.length > 0 && (
        <SubNavContainer>
          <SubNavigationPanel isOpen={isSubNavigationOpened}>
            {activeEntry ? activeEntry.code : 'No entry'}
            {subNavigationItems.map(item => (
              <div key={`${item.target}-${item.label}`}>
                {item.label} {JSON.stringify(item.section)}
              </div>
            ))}
          </SubNavigationPanel>
        </SubNavContainer>
      )}
    </NavContainer>
  );
};

export type {NavigationEntry};
export {PimNavigation};
