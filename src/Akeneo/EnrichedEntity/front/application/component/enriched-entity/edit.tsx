import * as React from 'react';
import {connect} from 'react-redux';
import {State} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import Sidebar from 'akeneoenrichedentity/application/component/app/sidebar';
import {Tab} from "akeneoenrichedentity/application/reducer/sidebar";
import editTabsProvider from 'akeneoenrichedentity/application/configuration/edit-tabs';

interface EditState {
  tabs: Tab[];
  currentTab: string;
}

interface EditProps extends EditState {}

class EnrichedEntityEditView extends React.Component<EditProps> {
  private tabView: JSX.Element;

  public props: EditProps;

  constructor(props: EditProps) {
    super(props);

    this.updateTabView(props.currentTab);
  }

  componentDidUpdate(nextProps: EditProps) {
    if (JSON.stringify(this.props.currentTab) !== JSON.stringify(nextProps.currentTab)) {
      this.updateTabView(this.props.currentTab);
    }
  }

  private async updateTabView(currentTab: string): Promise<void> {
    const TabView = await editTabsProvider.getView(currentTab);

    this.tabView = (<TabView />);
    this.forceUpdate();
  }

  render(): JSX.Element | JSX.Element[] {
    return (
      <div className="AknDefault-contentWithColumn">
        <div className="AknDefault-thirdColumnContainer">
          <div className="AknDefault-thirdColumn"></div>
        </div>
        <div className="AknDefault-contentWithBottom">
          {this.tabView}
        </div>
        <Sidebar />
      </div>
    );
  }
}

export default connect((state: State): EditState => {
  const tabs = undefined === state.sidebar.tabs ? [] : state.sidebar.tabs;
  const currentTab = undefined === state.sidebar.currentTab ? '' : state.sidebar.currentTab;

  return {
    tabs,
    currentTab
  }
})(EnrichedEntityEditView);
