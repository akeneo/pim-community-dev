import * as React from 'react';
import {connect} from 'react-redux';
import __ from 'akeneoreferenceentity/tools/translator';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {toggleSidebar, updateCurrentTab} from 'akeneoreferenceentity/application/event/sidebar';
import {Tab} from 'akeneoreferenceentity/application/reducer/sidebar';
import Key from 'akeneoreferenceentity/tools/key';

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

class Sidebar extends React.Component<SidebarProps> {
  props: SidebarProps;

  toggleSidebar = () => {
    this.props.events.toggleSidebar(!this.props.isCollapsed);
  };

  updateCurrentTab = (event: React.MouseEvent<HTMLSpanElement> | React.KeyboardEvent<HTMLSpanElement>) => {
    event.preventDefault();
    const target = event.target as HTMLSpanElement;
    if (undefined === target || undefined === target.dataset || undefined === target.dataset.tab) {
      return false;
    }

    this.props.events.updateCurrentTab(target.dataset.tab);

    return false;
  };

  render(): JSX.Element | JSX.Element[] {
    const colapsedClass = this.props.isCollapsed ? 'AknColumn--collapsed' : '';
    const BackButton = this.props.backButton;

    return (
      <div className={`AknColumn ${colapsedClass}`}>
        <div className="AknColumn-inner column-inner">
          <div className="AknColumn-innerTop">
            <div className="AknColumn-block">
              {undefined !== BackButton ? <BackButton /> : null}
              <div className="AknColumn-title">{__('pim_reference_entity.reference_entity.breadcrumb')}</div>
              {this.props.tabs.map((tab: any) => {
                const activeClass = this.props.currentTab === tab.code ? 'AknColumn-navigationLink--active' : '';

                return (
                  <span
                    key={tab.code}
                    role="button"
                    tabIndex={0}
                    className={`AknColumn-navigationLink ${activeClass}`}
                    data-tab={tab.code}
                    onClick={this.updateCurrentTab}
                    onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
                      if (Key.Space === event.key) this.updateCurrentTab(event);
                    }}
                  >
                    {__(tab.label)}
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
