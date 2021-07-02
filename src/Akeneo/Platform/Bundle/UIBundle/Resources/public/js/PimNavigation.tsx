import React, {FC, useMemo} from 'react';
import styled from 'styled-components';
import {PimView, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {IconProps, MainNavigationItem, SubNavigationItem, SubNavigationPanel} from 'akeneo-design-system';

type NavigationEntry = {
  code: string;
  title: string;
  disabled?: boolean;
  route: string;
  icon: React.ReactElement<IconProps>;
  subNavigations: SubNavigation[];
  isLandingSectionPage: boolean;
};

type SubNavigation = {
  title?: string;
  sections: SubNavigationSection[];
  entries: SubNavigationEntry[];
};

type SubNavigationEntry = {
  code: string;
  route: string;
  routeParams?: {[key: string]: any};
  title: string;
  sectionCode: string;
};

type SubNavigationSection = {
  code: string;
  title: string;
};

type Props = {
  entries: NavigationEntry[];
  activeEntryCode: string | null;
  activeSubEntryCode: string | null;
};
const PimNavigation: FC<Props> = ({entries, activeEntryCode, activeSubEntryCode}) => {
  const translate = useTranslate();
  const router = useRouter();

  const handleFollowEntry = (entry: NavigationEntry) => {
    router.redirect(router.generate(entry.route));
  };

  // @fixme: same than handleFollowEntry
  const handleFollowSubEntry = (event: any, subEntry: SubNavigationEntry) => {
    event.stopPropagation();
    event.preventDefault();
    router.redirect(router.generate(subEntry.route, subEntry.routeParams));
  };

  // @todo read default value by main navigation entry from Session storage
  // @example: collapsedColumn_pim-menu-settings: 1
  const isSubNavigationOpened = true;

  const activeNavigationEntry = useMemo((): NavigationEntry | undefined => {
    return entries.find((entry: NavigationEntry) => entry.code === activeEntryCode);
  }, [entries, activeEntryCode]);

  const activeSubNavigation = useMemo((): SubNavigation | undefined => {
    if (activeNavigationEntry) {
      return activeNavigationEntry.subNavigations.find((column: SubNavigation) => {
        return undefined !== column.entries.find((entry: SubNavigationEntry) => entry.code === activeSubEntryCode);
      });
    }

    return;
  }, [activeNavigationEntry, activeSubEntryCode]);

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
              active={entry.code === activeEntryCode}
              disabled={entry.disabled}
              icon={entry.icon}
              onClick={() => handleFollowEntry(entry)}
            >
              {translate(entry.title)}
            </MainNavigationItem>
          ))}
        </MenuContainer>
        <HelpContainer>
          <PimView viewName="pim-menu-help" />
        </HelpContainer>
      </MainNavContainer>
      {activeNavigationEntry &&
      (!activeNavigationEntry.isLandingSectionPage || activeSubEntryCode) &&
      activeSubNavigation &&
      activeSubNavigation.sections.length > 0 && (
        <SubNavContainer>
          <SubNavigationPanel isOpen={isSubNavigationOpened}>
            {activeSubNavigation.sections.map(section => {
              return <div key={section.code}>
                <SubNavigationTitle>{translate(section.title)}</SubNavigationTitle>
                {activeSubNavigation.entries.filter(subNav => subNav.sectionCode === section.code).map(subEntry => {
                  return (
                    <SubNavigationItem
                      active={subEntry.code === activeSubEntryCode}
                      key={subEntry.code}
                      href={`#${router.generate(subEntry.route, subEntry.routeParams)}`}
                      onClick={(event: any) => handleFollowSubEntry(event, subEntry)}
                    >
                      {subEntry.title}
                    </SubNavigationItem>
                  );
                })}
              </div>;
            })}
          </SubNavigationPanel>
        </SubNavContainer>
      )}
    </NavContainer>
  );
};

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

const SubNavigationTitle = styled.div`
  margin-bottom: 30px;
  color: #a1a9b7;
  text-transform: uppercase;
  font-size: 11px;
  line-height: 20px;
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

export type {NavigationEntry, SubNavigationSection, SubNavigation};
export {PimNavigation};
