import React, {FC} from 'react';
import styled from 'styled-components';
import { PimView, useTranslate } from "@akeneo-pim-community/shared";
import {IconProps, MainNavigationItem} from 'akeneo-design-system';

const LogoContainer = styled.div``;
const MenuContainer = styled.div``;
const HelpContainer = styled.div``;

type NavigationEntry = {
  code: string;
  label: string;
  active: boolean;
  disabled?: boolean;
  route: string;
  icon: React.ReactElement<IconProps>;
  position: number;
};

type Props = {
  entries: NavigationEntry[];
};
const PimNavigation: FC<Props> = ({entries}) => {
  const translate = useTranslate();

  return (
    <nav>
      <LogoContainer>
        <PimView viewName="pim-menu-logo" />
      </LogoContainer>
      <MenuContainer>
        {entries.map(({code, label, active, disabled, icon}) => (
          <MainNavigationItem key={code} active={active} disabled={disabled} icon={icon}>
            {translate(label)}
          </MainNavigationItem>
        ))}
      </MenuContainer>
      <HelpContainer>
        <PimView viewName="pim-menu-help" />
      </HelpContainer>
    </nav>
  );
};

export type {NavigationEntry};
export {PimNavigation};
