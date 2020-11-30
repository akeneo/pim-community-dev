import * as React from 'react';
import {connect} from 'react-redux';
import {EditState as State} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import Sidebar from 'akeneoassetmanager/application/component/app/sidebar';
import {Tab} from 'akeneoassetmanager/application/reducer/sidebar';
import sidebarProvider from 'akeneoassetmanager/application/configuration/sidebar';
import __ from 'akeneoassetmanager/tools/translator';
import {redirectToAssetFamilyListItem} from 'akeneoassetmanager/application/action/asset-family/router';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Locale, {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import Channel from 'akeneoassetmanager/domain/model/channel';
const router = require('pim/router');

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

interface EditProps extends StateProps, DispatchProps {}

class AssetFamilyEditView extends React.Component<EditProps> {
  public props: EditProps;
  private backToAssetFamilyList = () => (
    <a
      href={`#${router.generate('akeneo_asset_manager_asset_family_index')}`}
      role="button"
      tabIndex={0}
      className="AknColumn-navigationLink"
      onClick={e => {
        e.preventDefault();
        this.props.events.backToAssetFamilyList();
      }}
    >
      {__('pim_asset_manager.asset.button.back')}
    </a>
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
