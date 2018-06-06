import * as React from 'react';
import {connect} from 'react-redux';
import {State} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import Properties from 'akeneoenrichedentity/application/component/enriched-entity/edit/properties';
import Sidebar from 'akeneoenrichedentity/application/component/app/sidebar';
import {Tab} from "akeneoenrichedentity/application/reducer/sidebar";

interface EditState {
  tabs: Tab[];
  currentTab: string;
}

interface EditProps extends EditState {}

class EnrichedEntityEditView extends React.Component<EditProps> {
  props: EditProps;

  render(): JSX.Element | JSX.Element[] {
    const selectedTab: Tab|undefined = this.props.tabs.find(tab => tab.code === this.props.currentTab);

    return (
      <div className="AknDefault-contentWithColumn">
        <div className="AknDefault-thirdColumnContainer">
          <div className="AknDefault-thirdColumn"></div>
        </div>
        <div className="AknDefault-contentWithBottom">
            {
              (selectedTab && selectedTab.label === "Properties") &&
              <Properties />
            }
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
