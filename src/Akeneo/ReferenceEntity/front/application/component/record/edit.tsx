import React from 'react';
import {connect} from 'react-redux';
import styled from 'styled-components';
import {DeleteModal} from '@akeneo-pim-community/shared';
import {PimView} from '@akeneo-pim-community/legacy-bridge';
import {EditState as State} from 'akeneoreferenceentity/application/reducer/record/edit';
import Sidebar from 'akeneoreferenceentity/application/component/app/sidebar';
import {Tab} from 'akeneoreferenceentity/application/reducer/sidebar';
import sidebarProvider from 'akeneoreferenceentity/application/configuration/sidebar';
import {RefEntityBreadcrumb} from 'akeneoreferenceentity/application/component/app/breadcrumb';
import Image from 'akeneoreferenceentity/application/component/app/image';
import __ from 'akeneoreferenceentity/tools/translator';
import Record, {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {
  saveRecord,
  recordImageUpdated,
  backToReferenceEntity,
} from 'akeneoreferenceentity/application/action/record/edit';
import {deleteRecord} from 'akeneoreferenceentity/application/action/record/delete';
import EditState from 'akeneoreferenceentity/application/component/app/edit-state';
import File from 'akeneoreferenceentity/domain/model/file';
import Locale from 'akeneoreferenceentity/domain/model/locale';
import {localeChanged, channelChanged} from 'akeneoreferenceentity/application/action/record/user';
import LocaleSwitcher from 'akeneoreferenceentity/application/component/app/locale-switcher';
import ChannelSwitcher from 'akeneoreferenceentity/application/component/app/channel-switcher';
import denormalizeRecord from 'akeneoreferenceentity/application/denormalizer/record';
import Channel from 'akeneoreferenceentity/domain/model/channel';
import {openDeleteModal, cancelDeleteModal} from 'akeneoreferenceentity/application/event/confirmDelete';
import Key from 'akeneoreferenceentity/tools/key';
import {createLocaleReference} from 'akeneoreferenceentity/domain/model/locale-reference';
import {createChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';
import {getLocales} from 'akeneoreferenceentity/application/reducer/structure';
import {CompletenessBadge} from 'akeneoreferenceentity/application/component/app/completeness';
import {canEditReferenceEntity} from 'akeneoreferenceentity/application/reducer/right';
import {NormalizedCode} from 'akeneoreferenceentity/domain/model/record/code';
import {NormalizedCode as NormalizedAttributeCode} from 'akeneoreferenceentity/domain/model/product/attribute/code';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/product/attribute';
import {redirectToProductGrid} from 'akeneoreferenceentity/application/event/router';

const securityContext = require('pim/security-context');

const MetaContainer = styled.div`
  display: flex;
  align-items: center;
`;

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
  rights: {
    record: {
      edit: boolean;
      delete: boolean;
    };
  };
  record: NormalizedRecord;
  structure: {
    locales: Locale[];
    channels: Channel[];
  };
  confirmDelete: {
    isActive: boolean;
  };
  selectedAttribute: NormalizedAttribute | null;
  recordCode: NormalizedCode;
}

interface DispatchProps {
  events: {
    onSaveEditForm: () => void;
    onLocaleChanged: (locale: Locale) => void;
    onChannelChanged: (channel: Channel) => void;
    onImageUpdated: (image: File) => void;
    onDelete: (record: Record) => void;
    onOpenDeleteModal: () => void;
    onCancelDeleteModal: () => void;
    backToReferenceEntity: () => void;
    onRedirectToProductGrid: (selectedAttribute: NormalizedAttributeCode, recordCode: NormalizedCode) => void;
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
        if (Key.Space === event.key) this.props.events.backToReferenceEntity();
      }}
    >
      {__('pim_reference_entity.record.button.back')}
    </span>
  );

  private onConfirmedDelete = () => {
    const record = denormalizeRecord(this.props.record);
    this.props.events.onDelete(record);
  };

  private getSecondaryActions = (canDelete: boolean): JSX.Element | JSX.Element[] | null => {
    if (canDelete) {
      return (
        <div className="AknSecondaryActions AknDropdown AknButtonList-item">
          <div className="AknSecondaryActions-button dropdown-button" data-toggle="dropdown" />
          <div className="AknDropdown-menu AknDropdown-menu--right">
            <div className="AknDropdown-menuTitle">{__('pim_datagrid.actions.other')}</div>
            <div>
              <button className="AknDropdown-menuLink" onClick={() => this.props.events.onOpenDeleteModal()}>
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
    const completeness = record.getCompleteness(
      createChannelReference(this.props.context.channel),
      createLocaleReference(this.props.context.locale)
    );
    const isUsableSelectedAttributeOnTheGrid =
      null !== this.props.selectedAttribute && true === this.props.selectedAttribute.useable_as_grid_filter;

    return (
      <React.Fragment>
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
                    readOnly={true}
                  />
                  <div className="AknTitleContainer-mainContainer AknTitleContainer-mainContainer--contained">
                    <div>
                      <div className="AknTitleContainer-line">
                        <div className="AknTitleContainer-breadcrumbs">
                          <RefEntityBreadcrumb
                            referenceEntityIdentifier={record.getReferenceEntityIdentifier().stringValue()}
                            recordCode={record.getCode().stringValue()}
                          />
                        </div>
                        <div className="AknTitleContainer-buttonsContainer">
                          <div className="AknTitleContainer-userMenuContainer user-menu">
                            <PimView
                              className={`AknTitleContainer-userMenu ${
                                this.props.rights.record.edit ? '' : 'AknTitleContainer--withoutMargin'
                              }`}
                              viewName="pim-reference-entity-index-user-navigation"
                            />
                          </div>
                          {'product' === this.props.sidebar.currentTab ? (
                            isUsableSelectedAttributeOnTheGrid ? (
                              <div className="AknTitleContainer-actionsContainer AknButtonList">
                                <div className="AknTitleContainer-rightButton">
                                  <button
                                    className="AknButton AknButton--big AknButton--apply AknButton--centered"
                                    onClick={() =>
                                      this.props.events.onRedirectToProductGrid(
                                        (this.props.selectedAttribute as NormalizedAttribute).code,
                                        this.props.recordCode
                                      )
                                    }
                                  >
                                    {__('pim_reference_entity.record.product.not_enough_items.button')}
                                  </button>
                                </div>
                              </div>
                            ) : null
                          ) : (
                            <div className="AknTitleContainer-actionsContainer AknButtonList">
                              {this.getSecondaryActions(this.props.rights.record.delete)}
                              {this.props.rights.record.edit ? (
                                <div className="AknTitleContainer-rightButton">
                                  <button
                                    className="AknButton AknButton--apply"
                                    onClick={this.props.events.onSaveEditForm}
                                  >
                                    {__('pim_reference_entity.record.button.save')}
                                  </button>
                                </div>
                              ) : null}
                            </div>
                          )}
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
                            className="AknDropdown--right"
                            onChannelChange={this.props.events.onChannelChanged}
                          />
                          <LocaleSwitcher
                            localeCode={this.props.context.locale}
                            locales={this.props.structure.locales}
                            className="AknDropdown--right"
                            onLocaleChange={this.props.events.onLocaleChanged}
                          />
                        </div>
                      </div>
                    </div>
                    {completeness.hasRequiredAttribute() ? (
                      <MetaContainer>
                        {__('pim_common.completeness')}:&nbsp;
                        <CompletenessBadge completeness={completeness} />
                      </MetaContainer>
                    ) : null}
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
        {this.props.confirmDelete.isActive && (
          <DeleteModal
            title={__('pim_reference_entity.record.delete.title')}
            onConfirm={this.onConfirmedDelete}
            onCancel={this.props.events.onCancelDeleteModal}
          >
            {__('pim_reference_entity.record.delete.message', {recordLabel: label})}
          </DeleteModal>
        )}
      </React.Fragment>
    );
  }
}

export default connect(
  (state: State): StateProps => {
    const tabs = undefined === state.sidebar.tabs ? [] : state.sidebar.tabs;
    const currentTab = undefined === state.sidebar.currentTab ? '' : state.sidebar.currentTab;
    const locale = state.user.catalogLocale;

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
        channel: state.user.catalogChannel,
      },
      record: state.form.data,
      structure: {
        locales: getLocales(state.structure.channels, state.user.catalogChannel),
        channels: state.structure.channels,
      },
      rights: {
        record: {
          edit:
            securityContext.isGranted('akeneo_referenceentity_record_edit') &&
            canEditReferenceEntity(state.right.referenceEntity, state.form.data.reference_entity_identifier),
          delete:
            securityContext.isGranted('akeneo_referenceentity_record_edit') &&
            securityContext.isGranted('akeneo_referenceentity_record_delete') &&
            canEditReferenceEntity(state.right.referenceEntity, state.form.data.reference_entity_identifier),
        },
      },
      confirmDelete: state.confirmDelete,
      selectedAttribute: state.products.selectedAttribute,
      recordCode: state.form.data.code,
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onSaveEditForm: () => {
          dispatch(saveRecord());
        },
        onLocaleChanged: (locale: Locale) => {
          dispatch(localeChanged(locale.code));
        },
        onChannelChanged: (channel: Channel) => {
          dispatch(channelChanged(channel.code));
        },
        onImageUpdated: (image: File) => {
          dispatch(recordImageUpdated(image));
        },
        onDelete: (record: Record) => {
          dispatch(deleteRecord(record.getReferenceEntityIdentifier(), record.getCode()));
        },
        onOpenDeleteModal: () => {
          dispatch(openDeleteModal());
        },
        onCancelDeleteModal: () => {
          dispatch(cancelDeleteModal());
        },
        backToReferenceEntity: () => {
          dispatch(backToReferenceEntity());
        },
        onRedirectToProductGrid: (selectedAttribute: NormalizedAttributeCode, recordCode: NormalizedCode) => {
          dispatch(redirectToProductGrid(selectedAttribute, recordCode));
        },
      },
    };
  }
)(RecordEditView);
