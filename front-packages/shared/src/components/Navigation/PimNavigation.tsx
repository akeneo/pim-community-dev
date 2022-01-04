import React, {FC, useMemo} from 'react';
import styled, {css} from 'styled-components';
import {PimView} from '../PimView';
import {useRouter, useTranslate} from '../../hooks';
import {IconProps, LockIcon, MainNavigationItem, Tag, useTheme} from 'akeneo-design-system';
import {SubNavigation, SubNavigationEntry, SubNavigationType} from './SubNavigation';
import {useAnalytics} from '../../hooks';

const StyledMainNavigationItem = styled(MainNavigationItem)<{align?: 'bottom'; freeTrialEnabled: boolean}>`
  ${({align}) =>
    align === 'bottom' &&
    css`
      position: absolute;
      bottom: 0;
    `}

  ${({disabled, freeTrialEnabled}) =>
    disabled &&
    freeTrialEnabled &&
    css`
      cursor: pointer;
    `}
`;

type NavigationEntry = {
  code: string;
  title: string;
  disabled?: boolean;
  route: string;
  icon: React.ReactElement<IconProps>;
  subNavigations: SubNavigationType[];
  isLandingSectionPage: boolean;
  align?: 'bottom';
};

type Props = {
  entries: NavigationEntry[];
  activeEntryCode: string | null;
  activeSubEntryCode: string | null;
  freeTrialEnabled?: boolean;
};

const PimNavigation: FC<Props> = ({entries, activeEntryCode, activeSubEntryCode, freeTrialEnabled = false}) => {
  const translate = useTranslate();
  const router = useRouter();
  const theme = useTheme();
  const analytics = useAnalytics();

  const handleFollowEntry = (event: any, entry: NavigationEntry) => {
    event.stopPropagation();
    event.preventDefault();

    analytics.track('navigation:entry:clicked', {
      code: entry.code,
    });

    router.redirect(router.generate(entry.route));
  };

  const activeNavigationEntry = useMemo((): NavigationEntry | undefined => {
    return entries.find((entry: NavigationEntry) => entry.code === activeEntryCode);
  }, [entries, activeEntryCode]);

  const activeSubNavigation = useMemo((): SubNavigationType | undefined => {
    if (undefined === activeNavigationEntry) {
      return;
    }

    return activeNavigationEntry.subNavigations.find((column: SubNavigationType) => {
      return undefined !== column.entries.find((entry: SubNavigationEntry) => entry.code === activeSubEntryCode);
    });
  }, [activeNavigationEntry, activeSubEntryCode]);

  return (
    <NavContainer aria-label="Main navigation">
      <MainNavContainer>
        <LogoContainer>
          <PimView viewName="pim-menu-logo" />
        </LogoContainer>
        <MenuContainer>
          {entries.map(entry => (
            <StyledMainNavigationItem
              id={entry.code}
              key={entry.code}
              active={entry.code === activeEntryCode}
              disabled={entry.disabled}
              icon={entry.icon}
              onClick={event => handleFollowEntry(event, entry)}
              href={`#${router.generate(entry.route)}`}
              role="menuitem"
              data-testid="pim-main-menu-item"
              className={entry.code === activeEntryCode ? 'active' : undefined}
              align={entry.align}
              freeTrialEnabled={freeTrialEnabled}
            >
              {translate(entry.title)}
              {entry.disabled && freeTrialEnabled && (
                <LockIconContainer data-testid="locked-entry">
                  <StyledTag tint="blue">
                    <StyledLockIcon size={16} color={theme.color.blue100} />
                  </StyledTag>
                </LockIconContainer>
              )}
            </StyledMainNavigationItem>
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
          <SubNavigation
            entries={activeSubNavigation.entries}
            sections={activeSubNavigation.sections}
            backLink={activeSubNavigation.backLink}
            stateCode={activeSubNavigation.stateCode}
            title={activeSubNavigation.title}
            activeSubEntryCode={activeSubEntryCode}
            freeTrialEnabled={freeTrialEnabled}
          />
        )}
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
  width: 100%;
  flex-direction: column;
  justify-content: start;
  height: 100%;
  border-right: 1px solid ${({theme}) => theme.color.grey80};
  z-index: 803;
  background: white;
  overflow: auto;
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
