import React, {useState} from 'react';
import {Sidebar} from 'akeneoassetmanager/application/component/app/sidebar';
import sidebarProvider from 'akeneoassetmanager/application/configuration/sidebar';

type AssetFamilyEditProps = {
  initialTab: string;
  onTabChange: (tabCode: string) => void;
};

const AssetFamilyEdit = ({initialTab, onTabChange}: AssetFamilyEditProps) => {
  // TODO RAC-546: use a proper react router (be aware of the dynamic configuration)
  const tabs = sidebarProvider.getTabs('akeneo_asset_manager_asset_family_edit');
  const [currentTab, setCurrentTab] = useState(initialTab ?? tabs[0].code);
  const TabView = sidebarProvider.getView('akeneo_asset_manager_asset_family_edit', currentTab);

  return (
    <div className="AknDefault-contentWithColumn">
      <div className="AknDefault-thirdColumnContainer">
        <div className="AknDefault-thirdColumn" />
      </div>
      <div className="AknDefault-contentWithBottom">
        <div className="AknDefault-mainContent AknDefault-mainContent--withoutBottomPadding" data-tab={currentTab}>
          <TabView code={currentTab} />
        </div>
      </div>
      <Sidebar
        tabs={tabs}
        currentTab={currentTab}
        onTabChange={tab => {
          onTabChange(tab);
          setCurrentTab(tab);
        }}
      />
    </div>
  );
};

export {AssetFamilyEdit};
