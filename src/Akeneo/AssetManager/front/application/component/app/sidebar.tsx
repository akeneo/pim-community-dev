import React, {KeyboardEvent} from 'react';
import {MenuDropdown} from 'akeneoassetmanager/application/component/app/MenuDropdown';
import {getColor, getFontSize, Key, Link, useBooleanState} from 'akeneo-design-system';
import {useRouter, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';
import {Tab} from 'akeneoassetmanager/application/configuration/sidebar';

const BackLink = styled(Link)`
  display: block;
  font-size: ${getFontSize('big')};
  margin: 0 0 20px 0;
  color: ${getColor('grey', 120)};
`;

type SidebarProps = {
  tabs: Tab[];
  currentTab: string;
  onTabChange: (tabCode: string) => void;
};

const Sidebar = ({tabs, currentTab, onTabChange}: SidebarProps) => {
  const [isSidebarOpen, open, close] = useBooleanState();
  const router = useRouter();
  const translate = useTranslate();

  return (
    <div className={`AknColumn ${isSidebarOpen ? 'AknColumn--collapsed' : ''}`}>
      <div className="AknColumn-inner column-inner">
        <div className="AknColumn-navigation">
          <MenuDropdown
            label={translate('pim_asset_manager.asset_family.breadcrumb')}
            tabs={tabs}
            onTabChange={tab => onTabChange(tab.code)}
          />
        </div>
        <div className="AknColumn-innerTop">
          <div className="AknColumn-block">
            <BackLink decorated={false} href={`#${router.generate('akeneo_asset_manager_asset_family_index')}`}>
              {translate('pim_asset_manager.asset.button.back')}
            </BackLink>
            <div className="AknColumn-title">{translate('pim_asset_manager.asset_family.breadcrumb')}</div>
            {tabs.map((tab: any) => {
              const activeClass = currentTab === tab.code ? 'AknColumn-navigationLink--active' : '';

              return (
                <span
                  key={tab.code}
                  role="button"
                  tabIndex={0}
                  className={`AknColumn-navigationLink ${activeClass}`}
                  data-tab={tab.code}
                  onClick={() => onTabChange(tab.code)}
                  onKeyPress={(event: KeyboardEvent<HTMLSpanElement>) => {
                    if (Key.Space === event.key) onTabChange(tab.code);
                  }}
                >
                  {'string' === typeof tab.label ? translate(tab.label) : <tab.label />}
                </span>
              );
            })}
          </div>
        </div>
        <div className="AknColumn-innerBottom" />
      </div>
      <div className="AknColumn-collapseButton" onClick={() => (isSidebarOpen ? close() : open())} />
    </div>
  );
};

export {Sidebar};
