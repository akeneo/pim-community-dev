import React from 'react';
import styled from 'styled-components';
import {
  getColor,
  getFontSize,
  Link,
  SubNavigationItem,
  SubNavigationPanel,
  useBooleanState,
} from 'akeneo-design-system';
import {useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {MenuDropdown} from 'akeneoassetmanager/application/component/app/MenuDropdown';
import {Tab} from 'akeneoassetmanager/application/configuration/sidebar';

const BackLink = styled(Link)`
  font-size: ${getFontSize('big')};
  color: ${getColor('grey', 120)};
`;

const SectionTitle = styled.div`
  margin: 30px 0 20px;
  color: ${getColor('grey', 100)};
  text-transform: uppercase;
  font-size: ${getFontSize('small')};
  white-space: nowrap;
`;

type SidebarProps = {
  tabs: Tab[];
  currentTab: string;
  onTabChange: (tabCode: string) => void;
};

const Sidebar = ({tabs, currentTab, onTabChange}: SidebarProps) => {
  const [isSidebarOpen, open, close] = useBooleanState(true);
  const router = useRouter();
  const translate = useTranslate();

  return (
    <SubNavigationPanel
      isOpen={isSidebarOpen}
      open={open}
      close={close}
      closeTitle={translate('pim_common.close')}
      openTitle={translate('pim_common.open')}
    >
      <SubNavigationPanel.Collapsed>
        <MenuDropdown
          label={translate('pim_asset_manager.asset_family.breadcrumb')}
          tabs={tabs}
          onTabChange={({code}) => onTabChange(code)}
        />
      </SubNavigationPanel.Collapsed>
      <BackLink decorated={false} href={`#${router.generate('akeneo_asset_manager_asset_family_index')}`}>
        {translate('pim_asset_manager.asset.button.back')}
      </BackLink>
      <SectionTitle>{translate('pim_asset_manager.asset_family.breadcrumb')}</SectionTitle>
      {tabs.map(({code, label}) => (
        <SubNavigationItem
          key={code}
          id={code}
          active={code === currentTab}
          onClick={() => onTabChange(code)}
          role="menuitem"
        >
          {translate(label)}
        </SubNavigationItem>
      ))}
    </SubNavigationPanel>
  );
};

export {Sidebar};
