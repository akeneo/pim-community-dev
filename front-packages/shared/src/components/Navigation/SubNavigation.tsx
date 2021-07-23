import React, {FC, useEffect} from 'react';
import styled from 'styled-components';
import {SubNavigationItem, SubNavigationPanel, useBooleanState} from 'akeneo-design-system';
import {useRouter, useTranslate} from '../../hooks';
import {SubNavigationDropdown} from './SubNavigationDropdown';

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
};

const SubNavigation: FC<Props> = ({title, sections, entries, backLink, stateCode, activeSubEntryCode}) => {
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
          // @ts-ignore
          <Backlink onClick={() => router.redirectToRoute(backLink.route)}>{translate(backLink.title)}</Backlink>
        )}
        {sections.map(section => {
          return (
            <Section key={section.code}>
              <SectionTitle>{translate(section.title)}</SectionTitle>
              {entries
                .filter(subNav => subNav.sectionCode === section.code)
                .map(subEntry => (
                  <SubNavigationItem
                    active={subEntry.code === activeSubEntryCode}
                    key={subEntry.code}
                    href={`#${router.generate(subEntry.route, subEntry.routeParams)}`}
                    onClick={(event: any) => handleFollowSubEntry(event, subEntry)}
                    role="menuitem"
                  >
                    {subEntry.title}
                  </SubNavigationItem>
                ))}
            </Section>
          );
        })}
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

export {SubNavigation};
export type {SubNavigationType, SubNavigationSection, SubNavigationEntry};
