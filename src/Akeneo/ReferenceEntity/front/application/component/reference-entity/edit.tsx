import React from 'react';
import {connect} from 'react-redux';
import styled from 'styled-components';
import {EditState as State} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import Sidebar from 'akeneoreferenceentity/application/component/app/sidebar';
import {Tab} from 'akeneoreferenceentity/application/reducer/sidebar';
import sidebarProvider from 'akeneoreferenceentity/application/configuration/sidebar';
import CreateRecordModal from 'akeneoreferenceentity/application/component/record/create';
import __ from 'akeneoreferenceentity/tools/translator';
import {redirectToReferenceEntityListItem} from 'akeneoreferenceentity/application/action/reference-entity/router';
import Key from 'akeneoreferenceentity/tools/key';

const Container = styled.div`
  height: 100%;
  display: flex;
  flex-direction: column;
  padding: 0 40px;
`;

interface StateProps {
  sidebar: {
    tabs: Tab[];
    currentTab: string;
  };
  createRecord: {
    active: boolean;
  };
}

interface DispatchProps {
  events: {
    backToReferenceEntityList: () => void;
  };
}

interface EditProps extends StateProps, DispatchProps {}

class ReferenceEntityEditView extends React.Component<EditProps> {
  public props: EditProps;
  private backToReferenceEntityList = () => (
    <span
      role="button"
      tabIndex={0}
      className="AknColumn-navigationLink"
      onClick={this.props.events.backToReferenceEntityList}
      onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
        if (Key.Space === event.key) this.props.events.backToReferenceEntityList();
      }}
    >
      {__('pim_reference_entity.record.button.back')}
    </span>
  );

  render(): JSX.Element | JSX.Element[] {
    const TabView = sidebarProvider.getView(
      'akeneo_reference_entities_reference_entity_edit',
      this.props.sidebar.currentTab
    );

    return (
      <div className="AknDefault-contentWithColumn">
        <div className="AknDefault-thirdColumnContainer">
          <div className="AknDefault-thirdColumn" />
        </div>
        <div className="AknDefault-contentWithBottom">
          <Container data-tab={this.props.sidebar.currentTab}>
            <TabView code={this.props.sidebar.currentTab} />
          </Container>
        </div>
        <Sidebar backButton={this.backToReferenceEntityList} />
        {this.props.createRecord.active ? <CreateRecordModal /> : null}
      </div>
    );
  }
}

export default connect(
  (state: State): StateProps => {
    const tabs = undefined === state.sidebar.tabs ? [] : state.sidebar.tabs;
    const currentTab = undefined === state.sidebar.currentTab ? '' : state.sidebar.currentTab;

    return {
      sidebar: {
        tabs,
        currentTab,
      },
      createRecord: {
        active: state.createRecord.active,
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        backToReferenceEntityList: () => {
          dispatch(redirectToReferenceEntityListItem());
        },
      },
    };
  }
)(ReferenceEntityEditView);
