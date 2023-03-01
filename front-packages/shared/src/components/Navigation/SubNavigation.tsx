import React, {FC, useEffect} from 'react';
import styled, {css} from 'styled-components';
import {
  AkeneoThemedProps,
  Badge,
  LockIcon,
  SubNavigationItem,
  SubNavigationPanel,
  Tag,
  useBooleanState,
} from 'akeneo-design-system';
import {useRouter, useTranslate} from '../../hooks';
import {SubNavigationDropdown} from './SubNavigationDropdown';
import {useTheme} from 'akeneo-design-system';

type SubNavigationType = {
  title?: string;
  sections: SubNavigationSection[];
  entries: SubNavigationEntry[];
  backLink?: BackLink;
  stateCode?: string;
};

type SubNavigationEntry = {
  code: string;
  route: string;
  routeParams?: {[key: string]: any};
  title: string;
  sectionCode: string;
  disabled?: boolean;
  new?: boolean;
};

type SubNavigationSection = {
  code: string;
  title: string;
};

type BackLink = {
  title: string;
  route: string;
};

type Props = SubNavigationType & {
  activeSubEntryCode: string | null;
  freeTrialEnabled?: boolean;
};

const SubNavigation: FC<Props> = ({
  title,
  sections,
  entries,
  backLink,
  stateCode,
  activeSubEntryCode,
  freeTrialEnabled,
}) => {
  const translate = useTranslate();
  const router = useRouter();
  const subNavigationState = sessionStorage.getItem(`collapsedColumn_${stateCode}`);
  const [isSubNavigationOpened, openSubNavigation, closeSubNavigation] = useBooleanState(
    subNavigationState === null || subNavigationState === '1'
  );

  useEffect(() => {
    sessionStorage.setItem(`collapsedColumn_${stateCode}`, isSubNavigationOpened ? '1' : '0');
  }, [isSubNavigationOpened]);

  const handleFollowSubEntry = (event: any, subEntry: SubNavigationEntry) => {
    event.stopPropagation();
    event.preventDefault();
    router.redirect(router.generate(subEntry.route, subEntry.routeParams));
  };

  const theme = useTheme();

  return (
    <SubNavContainer role="menu" data-testid="pim-sub-menu">
      <SubNavigationPanel
        isOpen={isSubNavigationOpened}
        open={openSubNavigation}
        close={closeSubNavigation}
        closeTitle={translate('pim_common.close')}
        openTitle={translate('pim_common.open')}
      >
        <SubNavigationPanel.Collapsed>
          <SubNavigationDropdown entries={entries} title={title} />
        </SubNavigationPanel.Collapsed>
        {backLink && (
          <Backlink onClick={() => router.redirectToRoute(backLink.route)}>{translate(backLink.title)}</Backlink>
        )}
        {sections.map(section => {
          return (
            <Section key={section.code}>
              <SectionTitle>{translate(section.title)}</SectionTitle>
              {entries
                .filter(subNav => subNav.sectionCode === section.code)
                .map(subEntry => (
                  <StyledSubNavigationItem
                    id={subEntry.code}
                    active={subEntry.code === activeSubEntryCode}
                    key={subEntry.code}
                    href={subEntry.disabled ? undefined : `#${router.generate(subEntry.route, subEntry.routeParams)}`}
                    onClick={(event: any) => handleFollowSubEntry(event, subEntry)}
                    role="menuitem"
                    disabled={subEntry.disabled}
                    hasIconTag={subEntry.disabled && freeTrialEnabled}
                  >
                    {subEntry.title}
                    {subEntry.disabled && freeTrialEnabled && (
                      <Tag tint="blue">
                        <StyledLockIcon size={16} color={theme.color.blue100} />
                      </Tag>
                    )}
                    {subEntry.new && <StyledBadge level="secondary">{translate('pim_menu.tag.new')}</StyledBadge>}
                  </StyledSubNavigationItem>
                ))}
            </Section>
          );
        })}
        {/*
        PIM-10029: This div is added so that legacy modules could inject necessary content into sub-navigation panel
        such as filters. It is a shortcut until a proper solution is developed
        */}
        <div className="subnavigation-additional-container" />
      </SubNavigationPanel>
    </SubNavContainer>
  );
};

const SubNavContainer = styled.div``;

const SectionTitle = styled.div`
  margin-bottom: 20px;
  color: ${({theme}) => theme.color.grey100};
  text-transform: uppercase;
  font-size: 11px;
  line-height: 20px;
`;

const Section = styled.div`
  :not(:first-child) {
    margin-top: 30px;
  }
`;

const Backlink = styled.div`
  font-size: ${({theme}) => theme.fontSize.big};
  color: ${({theme}) => theme.color.grey140};
  cursor: pointer;
  padding-bottom: 10px;
`;

const StyledSubNavigationItem = styled(SubNavigationItem)<{disabled: boolean; hasIconTag: boolean} & AkeneoThemedProps>`
  ${Tag} {
    align-self: center;
    box-sizing: content-box;

    ${({hasIconTag}) =>
      hasIconTag &&
      css`
        height: 24px;
        padding: 0;
        box-sizing: border-box;
      `}

  ${({disabled}) =>
    disabled &&
    css`
      cursor: pointer;
    `}
`;

const StyledLockIcon = styled(LockIcon)`
  margin: 3px;
`;

const StyledBadge = styled(Badge)`
  margin-left: 10px;
  vertical-align: text-bottom;
`;

export {SubNavigation};
export type {SubNavigationType, SubNavigationSection, SubNavigationEntry};
