import React, {useEffect, useState} from 'react';
import {connect} from 'react-redux';
import styled from 'styled-components';
import {Checkbox, Toolbar, useSelection} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {DeleteModal} from '@akeneo-pim-community/shared';
import {Table} from 'akeneoreferenceentity/application/component/record/index/table';
import {NormalizedItemRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {redirectToRecord} from 'akeneoreferenceentity/application/action/record/router';
import ReferenceEntity, {
  denormalizeReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import Header from 'akeneoreferenceentity/application/component/reference-entity/edit/header';
import {recordCreationStart} from 'akeneoreferenceentity/domain/event/record/create';
import {deleteRecord} from 'akeneoreferenceentity/application/action/record/delete';
import {RefEntityBreadcrumb} from 'akeneoreferenceentity/application/component/app/breadcrumb';
import {
  completenessFilterUpdated,
  filterUpdated,
  needMoreResults,
  searchUpdated,
  updateRecordResults,
} from 'akeneoreferenceentity/application/action/record/search';
import {Column, Filter} from 'akeneoreferenceentity/application/reducer/grid';
import ReferenceEntityIdentifier, {
  createIdentifier as createReferenceIdentifier,
} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import RecordCode, {createCode as createRecordCode} from 'akeneoreferenceentity/domain/model/record/code';
import {cancelDeleteModal, openDeleteModal} from 'akeneoreferenceentity/application/event/confirmDelete';
import {
  CellView,
  FilterView,
  getDataCellView,
  getDataFilterView,
  hasDataFilterView,
} from 'akeneoreferenceentity/application/configuration/value';
import Locale from 'akeneoreferenceentity/domain/model/locale';
import Channel from 'akeneoreferenceentity/domain/model/channel';
import {catalogChannelChanged, catalogLocaleChanged} from 'akeneoreferenceentity/domain/event/user';
import {CompletenessValue} from 'akeneoreferenceentity/application/component/record/index/completeness-filter';
import {canEditReferenceEntity} from 'akeneoreferenceentity/application/reducer/right';
import {Attribute, NormalizedAttribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import denormalizeAttribute from 'akeneoreferenceentity/application/denormalizer/attribute/attribute';
import {MassDelete} from 'akeneoreferenceentity/application/component/reference-entity/edit/mass-delete/MassDelete';
import {getFilter} from 'akeneoreferenceentity/tools/filter';
import {createRecordSelectionQuery} from 'akeneoreferenceentity/domain/fetcher/fetcher';
const securityContext = require('pim/security-context');

const FullsizeToolbar = styled(Toolbar)`
  margin: 0 -40px;
`;

type StateProps = {
  context: {
    locale: string;
    channel: string;
  };
  referenceEntity: ReferenceEntity;
  grid: {
    records: NormalizedItemRecord[];
    columns: Column[];
    matchesCount: number;
    totalCount: number;
    isLoading: boolean;
    page: number;
    filters: Filter[];
  };
  attributes: NormalizedAttribute[] | null;
  rights: {
    record: {
      create: boolean;
      edit: boolean;
      delete: boolean;
    };
  };
  confirmDelete: {
    isActive: boolean;
    identifier?: string;
    label?: string;
  };
};

type DispatchProps = {
  events: {
    onRedirectToRecord: (record: NormalizedItemRecord) => void;
    onDeleteRecord: (referenceEntityIdentifier: ReferenceEntityIdentifier, recordCode: RecordCode) => void;
    onNeedMoreResults: () => void;
    onSearchUpdated: (userSearch: string) => void;
    onFilterUpdated: (filter: Filter) => void;
    onLocaleChanged: (locale: Locale) => void;
    onChannelChanged: (locale: Channel) => void;
    onCompletenessFilterUpdated: (completenessValue: CompletenessValue) => void;
    onRecordCreationStart: () => void;
    onFirstLoad: () => void;
    onOpenDeleteRecordModal: (recordCode: RecordCode, label: string) => void;
    onCancelDeleteModal: () => void;
  };
};

export type CellViews = {
  [key: string]: CellView;
};

export type FilterViews = {
  [key: string]: {
    view: FilterView;
    attribute: Attribute;
  };
};

const Records = ({
  context,
  grid,
  events,
  referenceEntity,
  rights,
  confirmDelete,
  attributes,
}: StateProps & DispatchProps) => {
  const [cellViews, setCellViews] = useState<CellViews>({});
  const [filterViews, setFilterViews] = useState<FilterViews>({});
  const translate = useTranslate();

  const [selection, selectionState, isItemSelected, onSelectionChange, onSelectAllChange, selectedCount] = useSelection<
    string
  >(grid.matchesCount);

  const searchValue = getFilter(grid.filters, 'full_text')?.value ?? '';

  const selectionQuery = createRecordSelectionQuery(
    referenceEntity.getIdentifier().stringValue(),
    selection,
    grid.filters,
    searchValue,
    context.channel,
    context.locale
  );

  useEffect(() => {
    events.onFirstLoad();
  }, []);

  useEffect(() => {
    let needToUpdateState = false;
    let newCellViews = cellViews;
    let newFilterViews = filterViews;

    if (0 === Object.keys(cellViews).length && 0 !== grid.columns.length) {
      newCellViews = grid.columns.reduce((cellViews: CellViews, column: Column): CellViews => {
        cellViews[column.key] = getDataCellView(column.type);

        return cellViews;
      }, {});

      needToUpdateState = true;
    }

    if (0 === Object.keys(filterViews).length && null !== attributes) {
      newFilterViews = attributes.reduce((filters: FilterViews, normalizedAttribute: NormalizedAttribute) => {
        const attribute = denormalizeAttribute(normalizedAttribute);

        if (hasDataFilterView(attribute.type)) {
          filters[attribute.getCode().stringValue()] = {
            view: getDataFilterView(attribute.type),
            attribute,
          };
        }

        return filters;
      }, {});

      needToUpdateState = true;
    }

    if (needToUpdateState) {
      setCellViews(newCellViews);
      setFilterViews(newFilterViews);
    }
  }, [grid, attributes]);

  useEffect(() => {
    onSelectAllChange(false);
  }, [grid.filters]);

  const isToolbarVisible = 0 < grid.matchesCount && !!selectionState && rights.record.delete;

  return (
    <>
      <Header
        label={referenceEntity.getLabel(context.locale)}
        image={referenceEntity.getImage()}
        primaryAction={() =>
          rights.record.create ? (
            <button className="AknButton AknButton--action" onClick={events.onRecordCreationStart}>
              {translate('pim_reference_entity.record.button.create')}
            </button>
          ) : null
        }
        withLocaleSwitcher={true}
        withChannelSwitcher={true}
        isDirty={false}
        isLoading={grid.isLoading}
        breadcrumb={<RefEntityBreadcrumb referenceEntityIdentifier={referenceEntity.getIdentifier().stringValue()} />}
        onLocaleChanged={events.onLocaleChanged}
        onChannelChanged={events.onChannelChanged}
        displayActions={rights.record.create}
      />
      {0 !== grid.totalCount ? (
        <Table
          onRedirectToRecord={!selectionState ? events.onRedirectToRecord : undefined}
          onDeleteRecord={events.onOpenDeleteRecordModal}
          onNeedMoreResults={events.onNeedMoreResults}
          onSearchUpdated={events.onSearchUpdated}
          onFilterUpdated={events.onFilterUpdated}
          onCompletenessFilterUpdated={events.onCompletenessFilterUpdated}
          recordCount={grid.matchesCount}
          locale={context.locale}
          channel={context.channel}
          grid={grid}
          cellViews={cellViews}
          filterViews={filterViews}
          referenceEntity={referenceEntity}
          rights={rights}
          isItemSelected={isItemSelected}
          onSelectionChange={onSelectionChange}
        />
      ) : (
        <div className="AknGridContainer-noData">
          <div className="AknGridContainer-noDataImage AknGridContainer-noDataImage--reference-entity" />
          <div className="AknGridContainer-noDataTitle">
            {translate('pim_reference_entity.record.no_data.title', {
              entityLabel: referenceEntity.getLabel(context.locale),
            })}
          </div>
          <div className="AknGridContainer-noDataSubtitle">
            {translate('pim_reference_entity.record.no_data.subtitle')}
          </div>
        </div>
      )}
      {confirmDelete.isActive && undefined !== confirmDelete.identifier && (
        <DeleteModal
          title={translate('pim_reference_entity.record.delete.title')}
          onConfirm={() => {
            events.onDeleteRecord(
              referenceEntity.getIdentifier(),
              createRecordCode(confirmDelete.identifier as string)
            );
            onSelectAllChange(false);
          }}
          onCancel={events.onCancelDeleteModal}
        >
          {translate('pim_reference_entity.record.delete.message', {
            recordLabel: confirmDelete.label ?? '',
          })}
        </DeleteModal>
      )}
      <FullsizeToolbar isVisible={isToolbarVisible}>
        <Toolbar.SelectionContainer>
          <Checkbox checked={selectionState} onChange={onSelectAllChange} />
        </Toolbar.SelectionContainer>
        <Toolbar.LabelContainer>
          {translate('pim_reference_entity.record.record_selected', {count: selectedCount}, selectedCount)}
        </Toolbar.LabelContainer>
        <Toolbar.ActionsContainer>
          {rights.record.delete && (
            <MassDelete
              selectedCount={selectedCount}
              referenceEntity={referenceEntity}
              selectionQuery={selectionQuery}
              onConfirm={() => onSelectAllChange(false)}
            />
          )}
        </Toolbar.ActionsContainer>
      </FullsizeToolbar>
    </>
  );
};

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
    const matchesCount =
      undefined === state.grid || undefined === state.grid.matchesCount ? 0 : state.grid.matchesCount;

    return {
      context: {
        locale: state.user.catalogLocale,
        channel: state.user.catalogChannel,
      },
      referenceEntity,
      grid: {
        records,
        matchesCount,
        totalCount: state.grid.totalCount,
        columns,
        isLoading: state.grid.isFetching,
        page,
        filters,
      },
      attributes: state.attributes.attributes,
      rights: {
        record: {
          create:
            securityContext.isGranted('akeneo_referenceentity_record_create') &&
            canEditReferenceEntity(state.right.referenceEntity, state.form.data.identifier),
          edit:
            securityContext.isGranted('akeneo_referenceentity_record_edit') &&
            canEditReferenceEntity(state.right.referenceEntity, state.form.data.identifier),
          delete:
            securityContext.isGranted('akeneo_referenceentity_record_create') &&
            securityContext.isGranted('akeneo_referenceentity_record_edit') &&
            securityContext.isGranted('akeneo_referenceentity_record_delete') &&
            canEditReferenceEntity(state.right.referenceEntity, state.form.data.identifier),
        },
      },
      confirmDelete: state.confirmDelete,
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onRedirectToRecord: (record: NormalizedItemRecord) => {
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
        onFilterUpdated: (filter: Filter) => {
          dispatch(filterUpdated(filter));
        },
        onCompletenessFilterUpdated: (completenessValue: CompletenessValue) => {
          dispatch(completenessFilterUpdated(completenessValue));
        },
        onRecordCreationStart: () => {
          dispatch(recordCreationStart());
        },
        onCancelDeleteModal: () => {
          dispatch(cancelDeleteModal());
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

type RecordLabelProps = {
  grid: {
    totalCount: number;
  };
};

const RecordLabel = ({grid}: RecordLabelProps) => {
  const translate = useTranslate();

  return (
    <>
      {translate('pim_reference_entity.reference_entity.tab.records')}
      <span className="AknColumn-span">({grid.totalCount})</span>
    </>
  );
};

export const label = connect(
  (state: EditState): RecordLabelProps => {
    return {
      grid: {
        totalCount: state.grid.totalCount,
      },
    };
  },
  () => {
    return {};
  }
)(RecordLabel);
