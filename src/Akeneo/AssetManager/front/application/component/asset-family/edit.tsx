import * as React from 'react';
import {connect} from 'react-redux';
import {EditState as State} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import Sidebar from 'akeneoassetmanager/application/component/app/sidebar';
import {Tab} from 'akeneoassetmanager/application/reducer/sidebar';
import sidebarProvider from 'akeneoassetmanager/application/configuration/sidebar';
import __ from 'akeneoassetmanager/tools/translator';
import {redirectToAssetFamilyListItem} from 'akeneoassetmanager/application/action/asset-family/router';
import Key from 'akeneoassetmanager/tools/key';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Locale, {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import Channel from 'akeneoassetmanager/domain/model/channel';

interface StateProps {
  locale: LocaleCode;
  assetFamily: AssetFamily;
  channels: Channel[];
  locales: Locale[];
  sidebar: {
    tabs: Tab[];
    currentTab: string;
  };
}

interface DispatchProps {
  events: {
    backToAssetFamilyList: () => void;
  };
}

export const breadcrumbConfiguration = [
  {
    action: {
      type: 'redirect',
      route: 'akeneo_asset_manager_asset_family_edit',
    },
    label: __('pim_asset_manager.asset_family.breadcrumb'),
  },
];

interface EditProps extends StateProps, DispatchProps {}

class AssetFamilyEditView extends React.Component<EditProps> {
  public props: EditProps;
  private backToAssetFamilyList = () => (
    <span
      role="button"
      tabIndex={0}
      className="AknColumn-navigationLink"
      onClick={this.props.events.backToAssetFamilyList}
      onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
        if (Key.Space === event.key) this.props.events.backToAssetFamilyList();
      }}
    >
      {__('pim_asset_manager.asset.button.back')}
    </span>
  );

  render(): JSX.Element | JSX.Element[] {
    const TabView = sidebarProvider.getView('akeneo_asset_manager_asset_family_edit', this.props.sidebar.currentTab);

    return (
      <div className="AknDefault-contentWithColumn">
        <div className="AknDefault-thirdColumnContainer">
          <div className="AknDefault-thirdColumn" />
        </div>
        <div className="AknDefault-contentWithBottom">
          <div
            className="AknDefault-mainContent AknDefault-mainContent--withoutBottomPadding"
            data-tab={this.props.sidebar.currentTab}
          >
            <TabView code={this.props.sidebar.currentTab} />
          </div>
        </div>
        <Sidebar backButton={this.backToAssetFamilyList} />
      </div>
    );
  }
}

export default connect(
  (state: State): StateProps => {
    const tabs = undefined === state.sidebar.tabs ? [] : state.sidebar.tabs;
    const currentTab = undefined === state.sidebar.currentTab ? '' : state.sidebar.currentTab;

    return {
      locale: state.user.catalogLocale,
      assetFamily: state.form.data,
      channels: state.structure.channels,
      locales: state.structure.locales,
      sidebar: {
        tabs,
        currentTab,
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        backToAssetFamilyList: () => {
          dispatch(redirectToAssetFamilyListItem());
        },
      },
    };
  }
)(AssetFamilyEditView);
