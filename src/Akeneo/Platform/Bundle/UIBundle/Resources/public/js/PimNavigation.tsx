import React, {FC, useEffect, useState} from 'react';
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

const SubNavEntry = styled.a`
  display: block;
  font-size: 15px;
  margin: 0 0 20px 0;
  color: #11324d;
  cursor: pointer;
  opacity: 0.85;
  transition: opacity 0.2s ease-in;
`;

const ActiveSubNavEntry = styled(SubNavEntry)`
  color: #9452ba;
`;

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
  code: string;
  position: number;
  route: string;
  routeParams?: {[key: string]: any}; // @fixme fix type?
  target: string;
  label: string;
  section?: any;
};

type NavigationEntry = {
  code: string;
  label: string;
  active: boolean;
  // @fixme  Find a better way to determine what is the active sub-navigation entry
  activeSubEntryCode: string;
  disabled?: boolean;
  route: string;
  icon: React.ReactElement<IconProps>;
  position: number;
  columns: SubNavigationEntry[][];
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
    // @fixme
    if (!entry.route) {
      console.error('Entry has no route', entry);
      return;
    }
    router.redirect(router.generate(entry.route));
  };

  // @fixme: same than handleFollowEntry
  const handleFollowSubEntry = (subEntry: SubNavigationEntry) => {
    // @fixme
    if (!subEntry.route) {
      console.error('sub-Entry has no route', subEntry);
      return;
    }
    router.redirect(router.generate(subEntry.route, subEntry.routeParams));
  };

  const [activeEntry, setActiveEntry] = useState<NavigationEntry | null>(null);
  const isActiveEntry = (code: string) => code === (activeEntry ? activeEntry.code : null);

  const [activeSubEntry, setActiveSubEntry] = useState<SubNavigationEntry | null>(null);
  const isActiveSubEntry = (code: string) => code === (activeSubEntry ? activeSubEntry.code : null);

  // @todo read default value by main navigation entry from Session storage
  // @example: collapsedColumn_pim-menu-settings: 1
  const isSubNavigationOpened = true;

  const getEntrySubNavigation = () => {
    if (!activeEntry) {
      return [];
    }

    if (activeSubEntry) {
      return activeEntry.columns.find((column: SubNavigationEntry[]) => {
        return undefined !== column.find((entry: SubNavigationEntry) => entry.code === activeSubEntry.code);
      });
    }

    return [];
  };

  console.log('entries', entries);

  const subNavigationItems = getEntrySubNavigation();

  useEffect(() => {
    const newActiveEntry = entries.find((entry: NavigationEntry) => entry.active);
    setActiveEntry(newActiveEntry || null);

    // @fixme find a better way to find the new activated sub-entry
    const newColumn = newActiveEntry
      ? newActiveEntry.columns.find((column: SubNavigationEntry[]) => {
          return column.find((entry: SubNavigationEntry) => entry.code === newActiveEntry.activeSubEntryCode);
        })
      : null;

    const newActiveSubEntry =
      newActiveEntry && newColumn
        ? newColumn.find((subEntry: SubNavigationEntry) => subEntry.code === newActiveEntry.activeSubEntryCode)
        : null;

    setActiveSubEntry(newActiveSubEntry || null);
  }, [entries]);

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
      {activeEntry &&
        (!activeEntry.isLandingSectionPage || activeSubEntry) &&
        subNavigationItems &&
        subNavigationItems.length > 0 && (
          <SubNavContainer>
            <SubNavigationPanel isOpen={isSubNavigationOpened}>
              {subNavigationItems.map(subEntry => {
                // @fixme: use DSM components
                if (isActiveSubEntry(subEntry.code)) {
                  return (
                    <ActiveSubNavEntry
                      key={`${subEntry.target}-${subEntry.label}`}
                      onClick={() => handleFollowSubEntry(subEntry)}
                    >
                      {subEntry.label}
                    </ActiveSubNavEntry>
                  );
                }
                return (
                  <SubNavEntry
                    key={`${subEntry.target}-${subEntry.label}`}
                    onClick={() => handleFollowSubEntry(subEntry)}
                  >
                    {subEntry.label}
                  </SubNavEntry>
                );
              })}
            </SubNavigationPanel>
          </SubNavContainer>
        )}
    </NavContainer>
  );
};

export type {NavigationEntry};
export {PimNavigation};
