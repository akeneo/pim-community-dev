import * as React from 'react';
import {connect} from 'react-redux';
import __ from 'akeneoassetmanager/tools/translator';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {toggleSidebar, updateCurrentTab} from 'akeneoassetmanager/application/event/sidebar';
import {Tab} from 'akeneoassetmanager/application/reducer/sidebar';
import {Key} from 'akeneo-design-system';
import DropdownMenu from 'akeneoassetmanager/application/component/app/dropdown-menu';

interface SidebarOwnProps {
  backButton?: () => JSX.Element;
}

interface SidebarState extends SidebarOwnProps {
  tabs: Tab[];
  currentTab: string;
  isCollapsed: boolean;
}

interface SidebarDispatch {
  events: {
    toggleSidebar: (isCollapsed: boolean) => void;
    updateCurrentTab: (tabCode: string) => void;
  };
}

interface SidebarProps extends SidebarState, SidebarDispatch {}

export type SidebarLabel = string | typeof React.Component;

class Sidebar extends React.Component<SidebarProps> {
  props: SidebarProps;

  toggleSidebar = () => {
    this.props.events.toggleSidebar(!this.props.isCollapsed);
  };

  updateCurrentTab = (tab: Tab) => {
    this.props.events.updateCurrentTab(tab.code);
  };

  render(): JSX.Element | JSX.Element[] {
    const colapsedClass = this.props.isCollapsed ? 'AknColumn--collapsed' : '';
    const BackButton = this.props.backButton;

    return (
      <div className={`AknColumn ${colapsedClass}`}>
        <div className="AknColumn-inner column-inner">
          <div className="AknColumn-navigation">
            <DropdownMenu
              label={__('pim_asset_manager.asset_family.breadcrumb')}
              elements={this.props.tabs}
              selectedElement={this.props.currentTab}
              onSelectionChange={this.updateCurrentTab}
            />
          </div>
          <div className="AknColumn-innerTop">
            <div className="AknColumn-block">
              {undefined !== BackButton ? <BackButton /> : null}
              <div className="AknColumn-title">{__('pim_asset_manager.asset_family.breadcrumb')}</div>
              {this.props.tabs.map((tab: any) => {
                const activeClass = this.props.currentTab === tab.code ? 'AknColumn-navigationLink--active' : '';

                return (
                  <span
                    key={tab.code}
                    role="button"
                    tabIndex={0}
                    className={`AknColumn-navigationLink ${activeClass}`}
                    data-tab={tab.code}
                    onClick={() => this.updateCurrentTab(tab)}
                    onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
                      if (Key.Space === event.key) this.updateCurrentTab(tab);
                    }}
                  >
                    {'string' === typeof tab.label ? __(tab.label) : <tab.label />}
                  </span>
                );
              })}
            </div>
          </div>
          <div className="AknColumn-innerBottom" />
        </div>
        <div className="AknColumn-collapseButton" onClick={this.toggleSidebar} />
      </div>
    );
  }
}

export default connect(
  (state: EditState, ownProps: SidebarOwnProps): SidebarState => {
    const tabs = undefined === state.sidebar.tabs ? [] : state.sidebar.tabs;
    const currentTab = undefined === state.sidebar.currentTab ? '' : state.sidebar.currentTab;
    const isCollapsed = undefined === state.sidebar.isCollapsed ? false : state.sidebar.isCollapsed;

    return {
      ...ownProps,
      tabs,
      currentTab,
      isCollapsed,
    };
  },
  (dispatch: any): SidebarDispatch => {
    return {
      events: {
        toggleSidebar: (isCollapsed: boolean) => {
          dispatch(toggleSidebar(isCollapsed));
        },
        updateCurrentTab: (tabCode: string) => {
          dispatch(updateCurrentTab(tabCode));
        },
      },
    };
  }
)(Sidebar);
