import * as React from 'react';
import {connect} from 'react-redux';
import Table from 'akeneoreferenceentity/application/component/record/index/table';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {redirectToRecord} from 'akeneoreferenceentity/application/action/record/router';
import __ from 'akeneoreferenceentity/tools/translator';
import ReferenceEntity, {
  denormalizeReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import Header from 'akeneoreferenceentity/application/component/reference-entity/edit/header';
import {recordCreationStart} from 'akeneoreferenceentity/domain/event/record/create';
import {deleteAllReferenceEntityRecords, deleteRecord} from 'akeneoreferenceentity/application/action/record/delete';
import {breadcrumbConfiguration} from 'akeneoreferenceentity/application/component/reference-entity/edit';
import {
  needMoreResults,
  searchUpdated,
  updateRecordResults,
  completenessFilterUpdated,
} from 'akeneoreferenceentity/application/action/record/search';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';
import ReferenceEntityIdentifier, {
  createIdentifier as createReferenceIdentifier,
} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import RecordCode, {createCode as createRecordCode} from 'akeneoreferenceentity/domain/model/record/code';
import DeleteModal from 'akeneoreferenceentity/application/component/app/delete-modal';
import {openDeleteModal, cancelDeleteModal} from 'akeneoreferenceentity/application/event/confirmDelete';
import {getDataCellView, CellView} from 'akeneoreferenceentity/application/configuration/value';
import {Filter} from 'akeneoreferenceentity/application/reducer/grid';
import Locale from 'akeneoreferenceentity/domain/model/locale';
import Channel from 'akeneoreferenceentity/domain/model/channel';
import {catalogLocaleChanged, catalogChannelChanged} from 'akeneoreferenceentity/domain/event/user';
import {CompletenessValue} from 'akeneoreferenceentity/application/component/record/index/completeness-filter';
import {canEditReferenceEntity} from 'akeneoreferenceentity/infrastructure/permission/edit';
import {getCatalogLocale} from 'akeneoreferenceentity/application/reducer/structure';

const securityContext = require('pim/security-context');

interface StateProps {
  context: {
    locale: string;
    channel: string;
  };
  referenceEntity: ReferenceEntity;
  grid: {
    records: NormalizedRecord[];
    columns: Column[];
    total: number;
    isLoading: boolean;
    page: number;
    filters: Filter[];
  };
  recordCount: number;
  rights: {
    record: {
      create: boolean;
      edit: boolean;
      deleteAll: boolean;
      delete: boolean;
    };
  };
  confirmDelete: {
    isActive: boolean;
    identifier?: string;
    label?: string;
  };
}

interface DispatchProps {
  events: {
    onRedirectToRecord: (record: NormalizedRecord) => void;
    onDeleteRecord: (referenceEntityIdentifier: ReferenceEntityIdentifier, recordCode: RecordCode) => void;
    onNeedMoreResults: () => void;
    onSearchUpdated: (userSearch: string) => void;
    onLocaleChanged: (locale: Locale) => void;
    onChannelChanged: (locale: Channel) => void;
    onCompletenessFilterUpdated: (completenessValue: CompletenessValue) => void;
    onDeleteAllRecords: (referenceEntity: ReferenceEntity) => void;
    onRecordCreationStart: () => void;
    onFirstLoad: () => void;
    onOpenDeleteAllRecordsModal: () => void;
    onOpenDeleteRecordModal: (recordCode: RecordCode, label: string) => void;
    onCancelDeleteModal: () => void;
  };
}

export type CellViews = {
  [key: string]: CellView;
};

const SecondaryAction = ({onOpenDeleteAllRecordsModal}: {onOpenDeleteAllRecordsModal: () => void}) => {
  return (
    <div className="AknSecondaryActions AknDropdown AknButtonList-item">
      <div className="AknSecondaryActions-button dropdown-button" data-toggle="dropdown" />
      <div className="AknDropdown-menu AknDropdown-menu--right">
        <div className="AknDropdown-menuTitle">{__('pim_datagrid.actions.other')}</div>
        <div>
          <button tabIndex={-1} className="AknDropdown-menuLink" onClick={() => onOpenDeleteAllRecordsModal()}>
            {__('pim_reference_entity.record.button.delete_all')}
          </button>
        </div>
      </div>
    </div>
  );
};

class Records extends React.Component<StateProps & DispatchProps, {cellViews: CellViews}> {
  state = {cellViews: {}};

  componentDidMount() {
    this.props.events.onFirstLoad();
  }

  static getDerivedStateFromProps(props: StateProps & DispatchProps, {cellViews}: {cellViews: CellViews}) {
    if (0 === Object.keys(cellViews).length && 0 !== props.grid.columns.length) {
      return {
        cellViews: props.grid.columns.reduce((cellViews: CellViews, column: Column): CellViews => {
          cellViews[column.key] = getDataCellView(column.type);

          return cellViews;
        }, {}),
      };
    }

    return null;
  }

  render() {
    const {context, grid, events, referenceEntity, rights, confirmDelete, recordCount} = this.props;

    return (
      <React.Fragment>
        <Header
          label={referenceEntity.getLabel(context.locale)}
          image={referenceEntity.getImage()}
          primaryAction={(defaultFocus: React.RefObject<any>) => {
            return rights.record.create ? (
              <button className="AknButton AknButton--action" onClick={events.onRecordCreationStart} ref={defaultFocus}>
                {__('pim_reference_entity.record.button.create')}
              </button>
            ) : null;
          }}
          secondaryActions={() => {
            return rights.record.deleteAll ? (
              <SecondaryAction
                onOpenDeleteAllRecordsModal={() => {
                  events.onOpenDeleteAllRecordsModal();
                }}
              />
            ) : null;
          }}
          withLocaleSwitcher={true}
          withChannelSwitcher={true}
          isDirty={false}
          isLoading={grid.isLoading}
          breadcrumbConfiguration={breadcrumbConfiguration}
          onLocaleChanged={events.onLocaleChanged}
          onChannelChanged={events.onChannelChanged}
          displayActions={this.props.rights.record.create || this.props.rights.record.deleteAll}
        />
        {0 !== recordCount ? (
          <Table
            onRedirectToRecord={events.onRedirectToRecord}
            onDeleteRecord={events.onOpenDeleteRecordModal}
            onNeedMoreResults={events.onNeedMoreResults}
            onSearchUpdated={events.onSearchUpdated}
            onCompletenessFilterUpdated={events.onCompletenessFilterUpdated}
            recordCount={recordCount}
            locale={context.locale}
            channel={context.channel}
            grid={grid}
            cellViews={this.state.cellViews}
            referenceEntity={referenceEntity}
            rights={rights}
          />
        ) : (
          <div className="AknGridContainer-noData">
            <div className="AknGridContainer-noDataImage AknGridContainer-noDataImage--reference-entity" />
            <div className="AknGridContainer-noDataTitle">
              {__('pim_reference_entity.record.no_data.title', {
                entityLabel: referenceEntity.getLabel(context.locale),
              })}
            </div>
            <div className="AknGridContainer-noDataSubtitle">{__('pim_reference_entity.record.no_data.subtitle')}</div>
          </div>
        )}
        {confirmDelete.isActive && undefined === confirmDelete.identifier && (
          <DeleteModal
            message={__('pim_reference_entity.record.delete_all.confirm', {
              entityIdentifier: referenceEntity.getIdentifier().stringValue(),
            })}
            title={__('pim_reference_entity.record.delete.title')}
            onConfirm={() => {
              events.onDeleteAllRecords(referenceEntity);
            }}
            onCancel={events.onCancelDeleteModal}
          />
        )}
        {confirmDelete.isActive && undefined !== confirmDelete.identifier && (
          <DeleteModal
            message={__('pim_reference_entity.record.delete.message', {
              recordLabel: confirmDelete.label,
            })}
            title={__('pim_reference_entity.record.delete.title')}
            onConfirm={() => {
              events.onDeleteRecord(
                referenceEntity.getIdentifier(),
                createRecordCode(confirmDelete.identifier as string)
              );
            }}
            onCancel={events.onCancelDeleteModal}
          />
        )}
      </React.Fragment>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const referenceEntity = denormalizeReferenceEntity(state.form.data);
    const records = undefined === state.grid || undefined === state.grid.items ? [] : state.grid.items;
    const page = undefined === state.grid || undefined === state.grid.query.page ? 0 : state.grid.query.page;
    const filters = undefined === state.grid || undefined === state.grid.query.filters ? [] : state.grid.query.filters;
    const columns =
      undefined === state.grid || undefined === state.grid.query || undefined === state.grid.query.columns
        ? []
        : state.grid.query.columns;
    const total = undefined === state.grid || undefined === state.grid.total ? 0 : state.grid.total;

    return {
      context: {
        locale: getCatalogLocale(state.structure.channels, state.user.catalogLocale, state.user.catalogLocale),
        channel: state.user.catalogChannel,
      },
      referenceEntity,
      grid: {
        records,
        total,
        columns,
        isLoading: state.grid.isFetching,
        page,
        filters,
      },
      recordCount: state.recordCount,
      rights: {
        record: {
          create: securityContext.isGranted('akeneo_referenceentity_record_create') && canEditReferenceEntity(),
          edit: securityContext.isGranted('akeneo_referenceentity_record_edit') && canEditReferenceEntity(),
          deleteAll:
            securityContext.isGranted('akeneo_referenceentity_record_edit') &&
            securityContext.isGranted('akeneo_referenceentity_records_delete_all') &&
            canEditReferenceEntity(),
          delete:
            securityContext.isGranted('akeneo_referenceentity_record_edit') &&
            securityContext.isGranted('akeneo_referenceentity_record_delete') &&
            canEditReferenceEntity(),
        },
      },
      confirmDelete: state.confirmDelete,
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onRedirectToRecord: (record: NormalizedRecord) => {
          dispatch(
            redirectToRecord(
              createReferenceIdentifier(record.reference_entity_identifier),
              createRecordCode(record.code)
            )
          );
        },
        onDeleteRecord: (referenceEntityIdentifier: ReferenceEntityIdentifier, recordCode: RecordCode) => {
          dispatch(deleteRecord(referenceEntityIdentifier, recordCode, true));
        },
        onNeedMoreResults: () => {
          dispatch(needMoreResults());
        },
        onSearchUpdated: (userSearch: string) => {
          dispatch(searchUpdated(userSearch));
        },
        onCompletenessFilterUpdated: (completenessValue: CompletenessValue) => {
          dispatch(completenessFilterUpdated(completenessValue));
        },
        onRecordCreationStart: () => {
          dispatch(recordCreationStart());
        },
        onDeleteAllRecords: (referenceEntity: ReferenceEntity) => {
          dispatch(deleteAllReferenceEntityRecords(referenceEntity));
        },
        onCancelDeleteModal: () => {
          dispatch(cancelDeleteModal());
        },
        onOpenDeleteAllRecordsModal: () => {
          dispatch(openDeleteModal());
        },
        onOpenDeleteRecordModal: (recordCode: RecordCode, label: string) => {
          dispatch(openDeleteModal(recordCode.stringValue(), label));
        },
        onLocaleChanged: (locale: Locale) => {
          dispatch(catalogLocaleChanged(locale.code));
          dispatch(updateRecordResults(false));
        },
        onChannelChanged: (channel: Channel) => {
          dispatch(catalogChannelChanged(channel.code));
          dispatch(updateRecordResults(false));
        },
        onFirstLoad: () => {
          dispatch(updateRecordResults(false));
        },
      },
    };
  }
)(Records);
