import * as React from 'react';
import {connect} from 'react-redux';
import {EditState as State} from 'akeneoreferenceentity/application/reducer/record/edit';
import Sidebar from 'akeneoreferenceentity/application/component/app/sidebar';
import {Tab} from 'akeneoreferenceentity/application/reducer/sidebar';
import sidebarProvider from 'akeneoreferenceentity/application/configuration/sidebar';
import Breadcrumb from 'akeneoreferenceentity/application/component/app/breadcrumb';
import Image from 'akeneoreferenceentity/application/component/app/image';
import __ from 'akeneoreferenceentity/tools/translator';
import PimView from 'akeneoreferenceentity/infrastructure/component/pim-view';
import Record, {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {
  saveRecord,
  deleteRecord,
  recordImageUpdated,
  backToReferenceEntity,
} from 'akeneoreferenceentity/application/action/record/edit';
import EditState from 'akeneoreferenceentity/application/component/app/edit-state';
const securityContext = require('pim/security-context');
import File from 'akeneoreferenceentity/domain/model/file';
import Locale from 'akeneoreferenceentity/domain/model/locale';
import {catalogLocaleChanged, catalogChannelChanged} from 'akeneoreferenceentity/domain/event/user';
import LocaleSwitcher from 'akeneoreferenceentity/application/component/app/locale-switcher';
import ChannelSwitcher from 'akeneoreferenceentity/application/component/app/channel-switcher';
import denormalizeRecord from 'akeneoreferenceentity/application/denormalizer/record';
import Channel from 'akeneoreferenceentity/domain/model/channel';

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
    channel: string;
  };
  acls: {
    create: boolean;
    delete: boolean;
  };
  record: NormalizedRecord;
  structure: {
    locales: Locale[];
    channels: Channel[];
  };
}

interface DispatchProps {
  events: {
    onSaveEditForm: () => void;
    onLocaleChanged: (locale: Locale) => void;
    onChannelChanged: (channel: Channel) => void;
    onImageUpdated: (image: File) => void;
    onDelete: (record: Record) => void;
    backToReferenceEntity: () => void;
  };
}

interface EditProps extends StateProps, DispatchProps {}

class RecordEditView extends React.Component<EditProps> {
  public props: EditProps;
  private backToReferenceEntity = () => (
    <span
      role="button"
      tabIndex={0}
      className="AknColumn-navigationLink"
      onClick={this.props.events.backToReferenceEntity}
      onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
        if (' ' === event.key) {
          this.props.events.backToReferenceEntity();
        }
      }}
    >
      {__('pim_reference_entity.record.button.back')}
    </span>
  );

  private onClickDelete = () => {
    const label = this.props.record.labels[this.props.context.locale];
    if (confirm(__('pim_reference_entity.record.delete.confirm', {'recordLabel': label}))) {
      const record = denormalizeRecord(this.props.record);

      this.props.events.onDelete(record);
    }
  };

  private getSecondaryActions = (canDelete: boolean): JSX.Element | JSX.Element[] | null => {
    if (canDelete) {
      return (
        <div className="AknSecondaryActions AknDropdown AknButtonList-item">
          <div className="AknSecondaryActions-button dropdown-button" data-toggle="dropdown" />
          <div className="AknDropdown-menu AknDropdown-menu--right">
            <div className="AknDropdown-menuTitle">{__('pim_datagrid.actions.other')}</div>
            <div>
              <button className="AknDropdown-menuLink" onClick={() => this.onClickDelete()}>
                {__('pim_reference_entity.record.button.delete')}
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
    const TabView = sidebarProvider.getView('akeneo_reference_entities_record_edit', this.props.sidebar.currentTab);

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
                  alt={__('pim_reference_entity.record.img', {'{{ label }}': label})}
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
                                route: 'akeneo_reference_entities_reference_entity_index',
                              },
                              label: __('pim_reference_entity.reference_entity.title'),
                            },
                            {
                              action: {
                                type: 'redirect',
                                route: 'akeneo_reference_entities_reference_entity_edit',
                                parameters: {
                                  identifier: record.getReferenceEntityIdentifier().stringValue(),
                                  tab: 'record',
                                },
                              },
                              label: record.getReferenceEntityIdentifier().stringValue(),
                            },
                            {
                              action: {
                                type: 'display',
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
                            viewName="pim-reference-entity-index-user-navigation"
                          />
                        </div>
                        <div className="AknButtonList">
                          {this.getSecondaryActions(this.props.acls.delete)}
                          <div className="AknTitleContainer-rightButton">
                            <button className="AknButton AknButton--apply" onClick={this.props.events.onSaveEditForm}>
                              {__('pim_reference_entity.record.button.save')}
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
                        <ChannelSwitcher
                          channelCode={this.props.context.channel}
                          channels={this.props.structure.channels}
                          locale={this.props.context.locale}
                          onChannelChange={this.props.events.onChannelChanged}
                        />
                        <LocaleSwitcher
                          localeCode={this.props.context.locale}
                          locales={this.props.structure.locales}
                          className="AknDropdown--left"
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
        <Sidebar backButton={this.backToReferenceEntity} />
      </div>
    );
  }
}

export default connect(
  (state: State): StateProps => {
    const tabs = undefined === state.sidebar.tabs ? [] : state.sidebar.tabs;
    const currentTab = undefined === state.sidebar.currentTab ? '' : state.sidebar.currentTab;
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;
    const channel =
      undefined === state.user || undefined === state.user.catalogChannel ? '' : state.user.catalogChannel;

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
        channel,
      },
      record: state.form.data,
      structure: {
        locales: state.structure.locales,
        channels: state.structure.channels,
      },
      acls: {
        create: securityContext.isGranted('akeneo_referenceentity_record_create'),
        delete: securityContext.isGranted('akeneo_referenceentity_record_delete'),
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
        onChannelChanged: (channel: Channel) => {
          dispatch(catalogChannelChanged(channel.code));
        },
        onImageUpdated: (image: File) => {
          dispatch(recordImageUpdated(image));
        },
        onDelete: (record: Record) => {
          dispatch(deleteRecord(record));
        },
        backToReferenceEntity: () => {
          dispatch(backToReferenceEntity());
        },
      },
    };
  }
)(RecordEditView);
