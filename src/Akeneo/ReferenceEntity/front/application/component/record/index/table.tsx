import React, {useState, useRef, useEffect} from 'react';
import styled from 'styled-components';
import {getColor} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {CommonRows} from 'akeneoreferenceentity/application/component/record/index/row/common';
import {ActionRows} from 'akeneoreferenceentity/application/component/record/index/row/action';
import {DetailRows} from 'akeneoreferenceentity/application/component/record/index/row/detail';
import NoResult from 'akeneoreferenceentity/application/component/app/no-result';
import {NormalizedItemRecord} from 'akeneoreferenceentity/domain/model/record/record';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';
import {CellViews, FilterViews} from 'akeneoreferenceentity/application/component/reference-entity/edit/record';
import {MAX_DISPLAYED_RECORDS} from 'akeneoreferenceentity/application/action/record/search';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import {getLabel} from 'pimui/js/i18n';
import {Filter} from 'akeneoreferenceentity/application/reducer/grid';
import {getFilter, getCompletenessFilter, getAttributeFilterKey} from 'akeneoreferenceentity/tools/filter';
import SearchField from 'akeneoreferenceentity/application/component/record/index/search-field';
import CompletenessFilter, {
  CompletenessValue,
} from 'akeneoreferenceentity/application/component/record/index/completeness-filter';
import ItemsCounter from 'akeneoreferenceentity/application/component/record/index/items-counter';
import {NormalizedAttributeIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';

const HorizontalScrollContainer = styled.div`
  overflow-x: auto;
  flex: 1;
  margin-left: -40px;
`;

const CheckboxHeaderCell = styled.th`
  position: sticky;
  top: 0;
  background: ${getColor('white')};
`;

interface TableState {
  locale: string;
  channel: string;
  grid: {
    records: NormalizedItemRecord[];
    columns: Column[];
    matchesCount: number;
    isLoading: boolean;
    page: number;
    filters: Filter[];
  };
  cellViews: CellViews;
  filterViews: FilterViews;
  recordCount: number;
  referenceEntity: ReferenceEntity;
  rights: {
    record: {
      create: boolean;
      edit: boolean;
      delete: boolean;
    };
  };
  onSelectionChange?: (recordCode: string, newValue: boolean) => void;
  isItemSelected: (recordCode: string) => boolean;
}

const columnCollectionsAreDifferent = (firstCollumnCollection: Column[], secondColumnCollection: Column[]): boolean => {
  return !(
    firstCollumnCollection.length === secondColumnCollection.length &&
    firstCollumnCollection.reduce(
      (allEqual: boolean, column: Column, index: number) => allEqual && column === secondColumnCollection[index],
      true
    )
  );
};

type RowView = React.FC<{
  isLoading: boolean;
  record: NormalizedItemRecord;
  locale: string;
  onRedirectToRecord: (record: NormalizedItemRecord) => void;
  onDeleteRecord: (recordCode: RecordCode, label: string) => void;
  position: number;
  columns: Column[];
  cellViews: CellViews;
}>;

interface TableDispatch {
  onRedirectToRecord?: (record: NormalizedItemRecord) => void;
  onDeleteRecord: (recordCode: RecordCode, label: string) => void;
  onNeedMoreResults: () => void;
  onSearchUpdated: (userSearch: string) => void;
  onFilterUpdated: (filter: Filter) => void;
  onCompletenessFilterUpdated: (completenessValue: CompletenessValue) => void;
}

interface TableProps extends TableState, TableDispatch {}

/**
 * This table is divided in three tables: one on the left to have sticky columns on common properties (common.tsx)
 * On the second table, you will have the additional properties of the records (details.tsx)
 * On the thrid one, you have all the actions of the record.
 */
const Table = ({
  grid,
  locale,
  channel,
  onRedirectToRecord,
  onDeleteRecord,
  onFilterUpdated,
  recordCount,
  cellViews,
  rights,
  filterViews,
  isItemSelected,
  onSelectionChange,
  onSearchUpdated,
  onNeedMoreResults,
  onCompletenessFilterUpdated,
  referenceEntity,
}: TableProps) => {
  const [columns, setColumns] = useState<Column[]>([]);
  const [needResize, setNeedResize] = useState<boolean>(false);
  const translate = useTranslate();

  const horizontalScrollContainerRef = useRef<HTMLDivElement>(null);
  const verticalScrollContainerRef = useRef<HTMLDivElement>(null);
  const detailTableRef = useRef<HTMLTableElement>(null);
  const commonTableRef = useRef<HTMLTableElement>(null);
  const actionTableRef = useRef<HTMLTableElement>(null);

  const handleScroll = () => {
    const verticalScrollContainer = verticalScrollContainerRef.current;
    const horizontalScrollContainer = horizontalScrollContainerRef.current;
    if (null !== verticalScrollContainer && null !== horizontalScrollContainer) {
      const scrollSize = verticalScrollContainer.offsetHeight;
      const scrollPosition = horizontalScrollContainer.scrollTop;
      const containerSize = horizontalScrollContainer.offsetHeight;
      const remainingHeightToBottom = scrollSize - scrollPosition - containerSize;
      if (remainingHeightToBottom < scrollSize / 3) {
        onNeedMoreResults();
      }
    }
  };

  const resizeScrollContainer = () => {
    const verticalScrollContainer = verticalScrollContainerRef.current;
    const horizontalScrollContainer = horizontalScrollContainerRef.current;
    const commonTable = commonTableRef.current;
    const detailTable = detailTableRef.current;
    const actionTable = actionTableRef.current;
    if (
      null !== verticalScrollContainer &&
      null !== horizontalScrollContainer &&
      null !== commonTable &&
      null !== detailTable &&
      null !== actionTable
    ) {
      const newWidth = commonTable.offsetWidth + detailTable.offsetWidth + actionTable.offsetWidth;
      const minWidth = horizontalScrollContainer.offsetWidth;
      if (
        newWidth !== verticalScrollContainer.offsetWidth ||
        detailTable.offsetWidth !== verticalScrollContainer.offsetWidth
      ) {
        verticalScrollContainer.style.width = `${newWidth}px`;
        verticalScrollContainer.style.minWidth = `${minWidth}px`;
      }
    }
  };

  const getColumnsToDisplay = (gridColumns: Column[], channel: string, locale: string) => {
    const columnsToDisplay = gridColumns.filter(
      (column: Column) => column.channel === channel && column.locale === locale
    );
    if (columnCollectionsAreDifferent(columnsToDisplay, columns)) {
      setColumns(columnsToDisplay);
    }

    return columnsToDisplay;
  };

  useEffect(() => {
    const detailTable = detailTableRef.current;
    const verticalScrollContainer = verticalScrollContainerRef.current;
    if (
      null !== detailTable &&
      null !== verticalScrollContainer &&
      detailTable.offsetWidth !== verticalScrollContainer.offsetWidth
    ) {
      setNeedResize(true);
      window.addEventListener('resize', resizeScrollContainer);
    }

    return () => {
      if (needResize) {
        window.removeEventListener('resize', resizeScrollContainer);
      }
    };
  }, [needResize]);

  const userSearch = getFilter(grid.filters, 'full_text')?.value ?? '';
  const completenessValue = getCompletenessFilter(grid.filters);
  const columnsToDisplay = getColumnsToDisplay(grid.columns, channel, locale);
  const noResult = 0 === grid.records.length && false === grid.isLoading;
  const placeholder = 0 === grid.records.length && grid.isLoading;

  useEffect(() => {
    if (needResize) {
      resizeScrollContainer();
    }
    const horizontalScrollContainer = horizontalScrollContainerRef.current;
    if (grid.page === 0 && null !== horizontalScrollContainer) {
      horizontalScrollContainer.scrollTop = 0;
    }
  }, [needResize, grid.page, grid.filters]);

  return (
    <>
      <div className="AknFilterBox AknFilterBox--search">
        <div className="AknFilterBox-list filter-box">
          <SearchField value={userSearch} onChange={onSearchUpdated} changeThreshold={250} />
          <ItemsCounter count={grid.matchesCount} />
          <div className="AknFilterBox-filterContainer AknFilterBox-filterContainer--inline">
            {Object.keys(filterViews).map((attributeCode: NormalizedAttributeIdentifier) => {
              const View = filterViews[attributeCode].view;
              const attribute = filterViews[attributeCode].attribute;
              const filter = grid.filters.find((filter: Filter) => filter.field === getAttributeFilterKey(attribute));

              return (
                <div
                  key={attribute.getCode().stringValue()}
                  className="AknFilterBox-filter AknFilterBox-filter--relative AknFilterBox-filter--smallMargin"
                  data-attribute={attribute.getCode().stringValue()}
                  data-type={attribute.getType()}
                >
                  <View attribute={attribute} filter={filter} onFilterUpdated={onFilterUpdated} />
                </div>
              );
            })}
          </div>
          <CompletenessFilter value={completenessValue} onChange={onCompletenessFilterUpdated} />
        </div>
      </div>
      {noResult ? (
        <NoResult entityLabel={referenceEntity.getLabel(locale)} />
      ) : (
        <HorizontalScrollContainer onScroll={handleScroll} ref={horizontalScrollContainerRef}>
          <div className="AknDefault-verticalScrollContainer" ref={verticalScrollContainerRef}>
            <table className="AknGrid AknGrid--light AknGrid--left" ref={commonTableRef}>
              <thead className="AknGrid-header">
                <tr className="AknGrid-bodyRow">
                  <CheckboxHeaderCell></CheckboxHeaderCell>
                  <th className="AknGrid-headerCell">{translate('pim_reference_entity.record.grid.column.image')}</th>
                  <th className="AknGrid-headerCell">{translate('pim_reference_entity.record.grid.column.label')}</th>
                  <th className="AknGrid-headerCell">{translate('pim_reference_entity.record.grid.column.code')}</th>
                  <th className="AknGrid-headerCell">
                    {translate('pim_reference_entity.record.grid.column.complete')}
                  </th>
                </tr>
              </thead>
              <tbody className="AknGrid-body">
                <CommonRows
                  records={grid.records}
                  locale={locale}
                  placeholder={placeholder}
                  onRedirectToRecord={onRedirectToRecord}
                  recordCount={recordCount}
                  isItemSelected={isItemSelected}
                  onSelectionChange={onSelectionChange}
                  canSelectRecord={rights.record.delete}
                />
              </tbody>
            </table>
            <table className="AknGrid AknGrid--light AknGrid--center" style={{flex: 1}} ref={detailTableRef}>
              <thead className="AknGrid-header">
                <tr className="AknGrid-bodyRow">
                  {0 === columnsToDisplay.length ? (
                    <th className="AknGrid-headerCell" />
                  ) : (
                    columnsToDisplay.map(column => (
                      <th key={column.key} className="AknGrid-headerCell">
                        {getLabel(column.labels, locale, column.code)}
                      </th>
                    ))
                  )}
                </tr>
              </thead>
              <tbody className="AknGrid-body">
                <DetailRows
                  records={grid.records}
                  locale={locale}
                  placeholder={placeholder}
                  onRedirectToRecord={onRedirectToRecord}
                  isItemSelected={isItemSelected}
                  onSelectionChange={onSelectionChange}
                  recordCount={recordCount}
                  columns={columnsToDisplay}
                  cellViews={cellViews}
                />
              </tbody>
            </table>
            <table className="AknGrid AknGrid--light AknGrid--right" ref={actionTableRef}>
              <thead className="AknGrid-header">
                <tr className="AknGrid-bodyRow">
                  <th className="AknGrid-headerCell AknGrid-headerCell--action" />
                </tr>
              </thead>
              <tbody className="AknGrid-body">
                <ActionRows
                  records={grid.records}
                  locale={locale}
                  placeholder={placeholder}
                  onDeleteRecord={onDeleteRecord}
                  recordCount={recordCount}
                  rights={rights}
                />
              </tbody>
            </table>
          </div>
          {grid.records.length >= MAX_DISPLAYED_RECORDS ? (
            <div className="AknDescriptionHeader AknDescriptionHeader--sticky">
              <div
                className="AknDescriptionHeader-icon"
                style={{backgroundImage: 'url("/bundles/pimui/images/illustrations/Product.svg")'}}
              />
              <div className="AknDescriptionHeader-title">
                {translate('pim_reference_entity.record.grid.more_result.title')}
                <div className="AknDescriptionHeader-description">
                  {translate('pim_reference_entity.record.grid.more_result.description', {total: grid.matchesCount})}
                </div>
              </div>
            </div>
          ) : null}
        </HorizontalScrollContainer>
      )}
    </>
  );
};

export {Table, RowView};
