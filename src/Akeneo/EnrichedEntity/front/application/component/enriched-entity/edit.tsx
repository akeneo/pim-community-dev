import * as React from 'react';
import {connect} from 'react-redux';
import {EditState as State} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import Sidebar from 'akeneoenrichedentity/application/component/app/sidebar';
import {Tab} from 'akeneoenrichedentity/application/reducer/sidebar';
import sidebarProvider from 'akeneoenrichedentity/application/configuration/sidebar';
import CreateRecordModal from 'akeneoenrichedentity/application/component/record/create';
import __ from 'akeneoenrichedentity/tools/translator';

interface StateProps {
  sidebar: {
    tabs: Tab[];
    currentTab: string;
  };
  createRecord: {
    active: boolean;
  };
}

interface DispatchProps {}

export const SecondaryAction = ({onDelete}: {onDelete: () => void}) => {
  return (
    <div className="AknSecondaryActions AknDropdown AknButtonList-item">
      <div className="AknSecondaryActions-button dropdown-button" data-toggle="dropdown" />
      <div className="AknDropdown-menu AknDropdown-menu--right">
        <div className="AknDropdown-menuTitle">{__('pim_datagrid.actions.other')}</div>
        <div>
          <button
            className="AknDropdown-menuLink"
            onClick={() => {
              if (confirm(__('pim_enriched_entity.enriched_entity.module.delete.confirm'))) {
                onDelete();
              }
            }}
          >
            {__('pim_enriched_entity.enriched_entity.module.delete.button')}
          </button>
        </div>
      </div>
    </div>
  );
};

export const breadcrumbConfiguration = [
  {
    action: {
      type: 'redirect',
      route: 'akeneo_enriched_entities_enriched_entity_edit',
    },
    label: __('pim_enriched_entity.enriched_entity.title'),
  },
];

interface EditProps extends StateProps, DispatchProps {}

class EnrichedEntityEditView extends React.Component<EditProps> {
  public props: EditProps;

  render(): JSX.Element | JSX.Element[] {
    const TabView = sidebarProvider.getView(
      'akeneo_enriched_entities_enriched_entity_edit',
      this.props.sidebar.currentTab
    );

    return (
      <div className="AknDefault-contentWithColumn">
        <div className="AknDefault-thirdColumnContainer">
          <div className="AknDefault-thirdColumn" />
        </div>
        <div className="AknDefault-contentWithBottom">
          <div className="AknDefault-mainContent" data-tab={this.props.sidebar.currentTab}>
            <TabView code={this.props.sidebar.currentTab} />
          </div>
        </div>
        <Sidebar />
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
  }
)(EnrichedEntityEditView);
