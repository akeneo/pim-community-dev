import * as React from 'react';
import {connect} from 'react-redux';
import {EditState as State} from 'akeneoenrichedentity/application/reducer/record/edit';
import Sidebar from 'akeneoenrichedentity/application/component/app/sidebar';
import {Tab} from 'akeneoenrichedentity/application/reducer/sidebar';
import sidebarProvider from 'akeneoenrichedentity/application/configuration/sidebar';
import Breadcrumb from 'akeneoenrichedentity/application/component/app/breadcrumb';
import Image from 'akeneoenrichedentity/application/component/app/image';
import __ from 'akeneoenrichedentity/tools/translator';
import PimView from 'akeneoenrichedentity/infrastructure/component/pim-view';
import Record, {NormalizedRecord} from 'akeneoenrichedentity/domain/model/record/record';
import {saveRecord, deleteRecord, recordImageUpdated} from 'akeneoenrichedentity/application/action/record/edit';
import EditState from 'akeneoenrichedentity/application/component/app/edit-state';
const securityContext = require('pim/security-context');
import File from 'akeneoenrichedentity/domain/model/file';
import Locale from 'akeneoenrichedentity/domain/model/locale';
import {catalogLocaleChanged} from 'akeneoenrichedentity/domain/event/user';
import LocaleSwitcher from 'akeneoenrichedentity/application/component/app/locale-switcher';
import denormalizeRecord from 'akeneoenrichedentity/application/denormalizer/record';

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
  acls: {
    create: boolean;
    delete: boolean;
  };
  record: NormalizedRecord;
  structure: {
    locales: Locale[];
  };
}

interface DispatchProps {
  events: {
    onSaveEditForm: () => void;
    onLocaleChanged: (locale: Locale) => void;
    onImageUpdated: (image: File) => void;
    onDelete: (record: Record) => void;
  };
}

interface EditProps extends StateProps, DispatchProps {}

class RecordEditView extends React.Component<EditProps> {
  public props: EditProps;

  private onClickDelete = (record: Record) => {
    if (confirm(__('pim_enriched_entity.record.module.delete.confirm'))) {
      this.props.events.onDelete(record);
    }
  };

  private getSecondaryActions = (record: Record, canDelete: boolean): JSX.Element | JSX.Element[] | null => {
    if (canDelete) {
      return (
        <div className="AknSecondaryActions AknDropdown AknButtonList-item">
          <div className="AknSecondaryActions-button dropdown-button" data-toggle="dropdown" />
          <div className="AknDropdown-menu AknDropdown-menu--right">
            <div className="AknDropdown-menuTitle">{__('pim_datagrid.actions.other')}</div>
            <div>
              <button className="AknDropdown-menuLink" onClick={() => this.onClickDelete(record)}>
                {__('pim_enriched_entity.record.module.delete.button')}
              </button>
            </div>
          </div>
        </div>
      );
    }

    return null;
  };

  render(): JSX.Element | JSX.Element[] {
    const editState = this.props.form.isDirty ? <EditState /> : '';
    const record = denormalizeRecord(this.props.record);
    const label = record.getLabel(this.props.context.locale);
    const TabView = sidebarProvider.getView('akeneo_enriched_entities_record_edit', this.props.sidebar.currentTab);
    return (
      <div className="AknDefault-contentWithColumn">
        <div className="AknDefault-thirdColumnContainer">
          <div className="AknDefault-thirdColumn" />
        </div>
        <div className="AknDefault-contentWithBottom">
          <div className="AknDefault-mainContent" data-tab={this.props.sidebar.currentTab}>
            <header className="AknTitleContainer">
              <div className="AknTitleContainer-line">
                <Image
                  alt={__('pim_enriched_entity.record.img', {'{{ label }}': label})}
                  image={record.getImage()}
                  onImageChange={this.props.events.onImageUpdated}
                />
                <div className="AknTitleContainer-mainContainer">
                  <div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-breadcrumbs">
                        <Breadcrumb
                          items={[
                            {
                              action: {
                                type: 'redirect',
                                route: 'akeneo_enriched_entities_enriched_entity_index',
                              },
                              label: __('pim_enriched_entity.enriched_entity.title'),
                            },
                            {
                              action: {
                                type: 'redirect',
                                route: 'akeneo_enriched_entities_enriched_entity_edit',
                                parameters: {
                                  identifier: record.getEnrichedEntityIdentifier().stringValue(),
                                  tab: 'record',
                                },
                              },
                              label: record.getEnrichedEntityIdentifier().stringValue(),
                            },
                            {
                              action: {
                                type: 'display'
                              },
                              label: record.getCode().stringValue(),
                            },
                          ]}
                        />
                      </div>
                      <div className="AknTitleContainer-buttonsContainer">
                        <div className="user-menu">
                          <PimView
                            className="AknTitleContainer-userMenu"
                            viewName="pim-enriched-entity-index-user-navigation"
                          />
                        </div>
                        <div className="AknButtonList">
                          {this.getSecondaryActions(record, this.props.acls.delete)}
                          <div className="AknTitleContainer-rightButton">
                            <button className="AknButton AknButton--apply" onClick={this.props.events.onSaveEditForm}>
                              {__('pim_enriched_entity.record.button.save')}
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-title">{label}</div>
                      {editState}
                    </div>
                  </div>
                  <div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-context AknButtonList">
                        <LocaleSwitcher
                          localeCode={this.props.context.locale}
                          locales={this.props.structure.locales}
                          onLocaleChange={this.props.events.onLocaleChanged}
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </header>
            <div className="content">
              <TabView code={this.props.sidebar.currentTab} />
            </div>
          </div>
        </div>
        <Sidebar />
      </div>
    );
  }
}

export default connect(
  (state: State): StateProps => {
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
        locale,
      },
      record: state.form.data,
      structure: {
        locales: state.structure.locales,
      },
      acls: {
        create: securityContext.isGranted('akeneo_enrichedentity_record_create'),
        delete: securityContext.isGranted('akeneo_enrichedentity_record_delete'),
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onSaveEditForm: () => {
          dispatch(saveRecord());
        },
        onLocaleChanged: (locale: Locale) => {
          dispatch(catalogLocaleChanged(locale.code));
        },
        onImageUpdated: (image: File) => {
          dispatch(recordImageUpdated(image));
        },
        onDelete: (record: Record) => {
          dispatch(deleteRecord(record));
        },
      },
    };
  }
)(RecordEditView);
