import * as React from 'react';
import {connect} from 'react-redux';
import {EditState as State} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import Sidebar from 'akeneoenrichedentity/application/component/app/sidebar';
import {Tab} from 'akeneoenrichedentity/application/reducer/sidebar';
import editTabsProvider from 'akeneoenrichedentity/application/configuration/edit-tabs';
import Breadcrumb from 'akeneoenrichedentity/application/component/app/breadcrumb';
import Image from 'akeneoenrichedentity/application/component/app/image';
import __ from 'akeneoenrichedentity/tools/translator';
import PimView from 'akeneoenrichedentity/infrastructure/component/pim-view';
import EnrichedEntity, {denormalizeEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {saveEnrichedEntity} from 'akeneoenrichedentity/application/action/enriched-entity/edit';
import EditState from 'akeneoenrichedentity/application/component/app/edit-state';
import {recordCreationStart} from 'akeneoenrichedentity/domain/event/record/create';
import CreateRecordModal from 'akeneoenrichedentity/application/component/record/create';
const securityContext = require('pim/security-context');
import ImageModel from 'akeneoenrichedentity/domain/model/image';
import {enrichedEntityImageUpdated} from 'akeneoenrichedentity/application/action/enriched-entity/edit';

interface StateProps {
  sidebar: {
    tabs: Tab[];
    currentTab: string;
  };
  form: {
    isDirty: boolean;
  };
  context: {
    locale: string;
  };
  createRecord: {
    active: boolean;
  },
  acls: {
    create: boolean;
  },
  enrichedEntity: EnrichedEntity;
}

interface DispatchProps {
  events: {
    onSaveEditForm: () => void;
    onRecordCreationStart: () => void;
    onImageUpdated: (image: ImageModel|null) => void;
  }
}

interface EditProps extends StateProps, DispatchProps {}

class EnrichedEntityEditView extends React.Component<EditProps> {
  private tabView: JSX.Element;

  public props: EditProps;

  constructor(props: EditProps) {
    super(props);

    this.updateTabView(props.sidebar.currentTab);
  }

  componentDidUpdate(nextProps: EditProps) {
    if (JSON.stringify(this.props.sidebar.currentTab) !== JSON.stringify(nextProps.sidebar.currentTab)) {
      this.updateTabView(this.props.sidebar.currentTab);
    }
  }

  private updateTabView = async (currentTab: string): Promise<void> => {
    const TabView = await editTabsProvider.getView(currentTab);

    this.tabView = (<TabView code={currentTab} />);
    this.forceUpdate();
  }

  private getHeaderButton = (canCreate: boolean, currentTab: string): JSX.Element | JSX.Element[] => {
    if (currentTab === 'pim-enriched-entity-edit-form-records' && canCreate) {
      return (
        <button className="AknButton AknButton--apply" onClick={this.props.events.onRecordCreationStart}>
          {__('pim_enriched_entity.button.create')}
        </button>
      );
    }

    return (
      <button
        className="AknButton AknButton--apply"
        onClick={this.props.events.onSaveEditForm}
      >
        {__('pim_enriched_entity.button.save')}
      </button>
    );
  };

  render(): JSX.Element | JSX.Element[] {
    const editState = this.props.form.isDirty ? <EditState /> : '';
    const label = this.props.enrichedEntity.getLabel(this.props.context.locale);

    return (
      <div className="AknDefault-contentWithColumn">
        <div className="AknDefault-thirdColumnContainer">
          <div className="AknDefault-thirdColumn"></div>
        </div>
        <div className="AknDefault-contentWithBottom">
          <div className="AknDefault-mainContent" data-tab={this.props.sidebar.currentTab}>
            <header className="AknTitleContainer">
              <div className="AknTitleContainer-line">
                <Image alt={__('pim_enriched_entity.enriched_entity.img', {'{{ label }}': label})} image={this.props.enrichedEntity.getImage()} onImageChange={this.props.events.onImageUpdated}/>
                <div className="AknTitleContainer-mainContainer">
                  <div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-breadcrumbs">
                        <Breadcrumb items={[
                          {
                            action: {
                              type: 'redirect',
                              route: 'akeneo_enriched_entities_enriched_entity_edit'
                            },
                            label: __('pim_enriched_entity.enriched_entity.title')
                          }
                        ]}/>
                      </div>
                      <div className="AknTitleContainer-buttonsContainer">
                        <div className="user-menu">
                          <PimView className="AknTitleContainer-userMenu" viewName="pim-enriched-entity-index-user-navigation"/>
                        </div>
                        <div className="AknButtonList" >
                          <div className="AknTitleContainer-rightButton">
                            {this.getHeaderButton(this.props.acls.create, this.props.sidebar.currentTab)}
                          </div>
                        </div>
                      </div>
                    </div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-title">
                        {label}
                      </div>
                      {editState}
                    </div>
                  </div>
                  <div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-context AknButtonList"></div>
                    </div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-meta AknButtonList"></div>
                    </div>
                  </div>
                </div>
              </div>
            </header>
            <div className="content">
              {this.tabView}
            </div>
          </div>
        </div>
        <Sidebar />
        {this.props.createRecord.active ? <CreateRecordModal /> : null}
      </div>
    );
  }
}

export default connect((state: State): StateProps => {
  const enrichedEntity = denormalizeEnrichedEntity(state.form.data);
  const tabs = undefined === state.sidebar.tabs ? [] : state.sidebar.tabs;
  const currentTab = undefined === state.sidebar.currentTab ? '' : state.sidebar.currentTab;
  const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;

  return {
    sidebar: {
      tabs,
      currentTab,
    },
    form: {
      isDirty: state.form.state.isDirty,
    },
    context: {
      locale
    },
    enrichedEntity,
    createRecord: {
      active: state.createRecord.active
    },
    acls: {
      create: securityContext.isGranted('akeneo_enrichedentity_record_create')
    }
  }
}, (dispatch: any): DispatchProps => {
  return {
    events: {
      onSaveEditForm: () => {
        dispatch(saveEnrichedEntity());
      },
      onRecordCreationStart: () => {
        dispatch(recordCreationStart());
      },
      onImageUpdated: (image: ImageModel|null) => {
        dispatch(enrichedEntityImageUpdated(image));
      }
    }
  }
})(EnrichedEntityEditView);
