import React, {FC, useMemo} from 'react';
import styled from 'styled-components';
import {PimView} from '../PimView';
import {useRouter, useTranslate} from '../../hooks';
import {IconProps, LockIcon, MainNavigationItem, Tag} from 'akeneo-design-system';
import {SubNavigation, SubNavigationEntry, SubNavigationType} from './SubNavigation';

type NavigationEntry = {
  code: string;
  title: string;
  disabled?: boolean;
  route: string;
  icon: React.ReactElement<IconProps>;
  subNavigations: SubNavigationType[];
  isLandingSectionPage: boolean;
};

type Props = {
  entries: NavigationEntry[];
  activeEntryCode: string | null;
  activeSubEntryCode: string | null;
  freeTrialEnabled: boolean;
};
const PimNavigation: FC<Props> = ({entries, activeEntryCode, activeSubEntryCode, freeTrialEnabled}) => {
  const translate = useTranslate();
  const router = useRouter();

  const handleFollowEntry = (entry: NavigationEntry) => {
    router.redirect(router.generate(entry.route));
  };

  const activeNavigationEntry = useMemo((): NavigationEntry | undefined => {
    return entries.find((entry: NavigationEntry) => entry.code === activeEntryCode);
  }, [entries, activeEntryCode]);

  const activeSubNavigation = useMemo((): SubNavigationType | undefined => {
    if (activeNavigationEntry) {
      return activeNavigationEntry.subNavigations.find((column: SubNavigationType) => {
        return undefined !== column.entries.find((entry: SubNavigationEntry) => entry.code === activeSubEntryCode);
      });
    }

    return;
  }, [activeNavigationEntry, activeSubEntryCode]);

  return (
    <NavContainer aria-label="Main navigation">
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
              role='menuitem'
              data-testid='pim-main-menu-item'
              className={entry.code === activeEntryCode ? 'active' : undefined}
              style={entry.disabled && freeTrialEnabled ? {cursor: 'pointer'} : {}}
            >
              {translate(entry.title)}
              {entry.disabled && freeTrialEnabled &&
                <LockIconContainer>
                  <StyledTag tint="blue">
                    <StyledLockIcon size={16} color={'#5992c7'}/>
                  </StyledTag>
                </LockIconContainer>
              }
            </MainNavigationItem>
          ))}
        </MenuContainer>
        <HelpContainer>
          <PimView viewName="pim-menu-help" />
        </HelpContainer>
      </MainNavContainer>
      {
        activeNavigationEntry &&
        (!activeNavigationEntry.isLandingSectionPage || activeSubEntryCode) &&
        activeSubNavigation &&
        activeSubNavigation.sections.length > 0 &&
          <SubNavigation
            entries={activeSubNavigation.entries}
            sections={activeSubNavigation.sections}
            backLink={activeSubNavigation.backLink}
            stateCode={activeSubNavigation.stateCode}
            title={activeSubNavigation.title}
            activeSubEntryCode={activeSubEntryCode}
          />
      }
    </NavContainer>
  );
};

const StyledTag = styled(Tag)`
  padding: 0;
  height: 24px;
  width: 24px;
`;

const StyledLockIcon = styled(LockIcon)`
  margin: 3px;
`;

const LockIconContainer = styled.div`
  position: absolute;
  top: 0;
  right: 12px;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
`;

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

const LogoContainer = styled.div`
  height: 80px;
  min-height: 80px;
  position: relative;
`;

const MenuContainer = styled.div`
  position: relative;
  height: 100%;
`;

const HelpContainer = styled.div`
  height: 80px;
  min-height: 80px;
  position: relative;
  margin-top: auto;
`;

export type {NavigationEntry, SubNavigation};
export {PimNavigation};
