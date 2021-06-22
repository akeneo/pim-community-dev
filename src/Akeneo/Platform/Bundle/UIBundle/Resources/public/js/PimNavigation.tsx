import React, {FC, useState} from 'react';
import styled from 'styled-components';
import {PimView, useTranslate} from '@akeneo-pim-community/shared';
import {IconProps, MainNavigationItem} from 'akeneo-design-system';

const LogoContainer = styled.div``;
const MenuContainer = styled.div``;
const HelpContainer = styled.div``;

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
};

type Props = {
  entries: NavigationEntry[];
};
const PimNavigation: FC<Props> = ({entries}) => {
  const translate = useTranslate();
  const handleFollowEntry = (code: string) => {
    setActiveEntry(code);
  };

  // @todo initial state: set the initial state with the active entry or the first of the list
  const [activeEntry, setActiveEntry] = useState<string>(entries[0].code || '');
  const isActiveEntry = (code: string) => code === activeEntry;

  const getEntrySubNavigation = () => {
    const entry = entries.find((entry: NavigationEntry) => entry.code === activeEntry);

    if (!entry) {
      return [];
    }
    console.log('getEntrySubNavigation', entry);
    return entry.items;
  };

  const subNavigationItems = getEntrySubNavigation();

  return (
    <nav>
      <LogoContainer>
        <PimView viewName="pim-menu-logo" />
      </LogoContainer>
      <MenuContainer>
        {entries.map(({code, label, disabled, icon}) => (
          <MainNavigationItem
            key={code}
            active={isActiveEntry(code)}
            disabled={disabled}
            icon={icon}
            onClick={() => handleFollowEntry(code)}
          >
            {translate(label)}
          </MainNavigationItem>
        ))}
      </MenuContainer>
      <HelpContainer>
        <PimView viewName="pim-menu-help" />
      </HelpContainer>
      <div>
        {activeEntry}
        {subNavigationItems.map(item => (
          <div key={`${item.target}-${item.label}`}>
            {item.label} {JSON.stringify(item.section)}
          </div>
        ))}
      </div>
    </nav>
  );
};

export type {NavigationEntry};
export {PimNavigation};
