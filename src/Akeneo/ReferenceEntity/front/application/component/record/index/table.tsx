import CommonView from 'akeneoreferenceentity/application/component/record/index/common';
import ActionView from 'akeneoreferenceentity/application/component/record/index/action';
import DetailsView from 'akeneoreferenceentity/application/component/record/index/detail';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';
import {CellViews} from 'akeneoreferenceentity/application/component/reference-entity/edit/record';
import {MAX_DISPLAYED_RECORDS} from 'akeneoreferenceentity/application/action/record/search';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import {getLabel} from 'pimenrich/js/i18n';

interface TableState {
  locale: string;
  channel: string;
  grid: {
    records: NormalizedRecord[];
    columns: Column[];
    total: number;
    isLoading: boolean;
    page: number;
  };
  cellViews: CellViews;
  recordCount: number;
  referenceEntity: ReferenceEntity;
}

export type RowView = React.SFC<{
  isLoading: boolean;
  record: NormalizedRecord;
  locale: string;
  onRedirectToRecord: (record: NormalizedRecord) => void;
  onDeleteRecord: (recordCode: RecordCode, label: string) => void;
  position: number;
  columns: Column[];
  cellViews: CellViews;
}>;

interface TableDispatch {
  onRedirectToRecord: (record: NormalizedRecord) => void;
  onDeleteRecord: (recordCode: RecordCode, label: string) => void;
  onNeedMoreResults: () => void;
  onSearchUpdated: (userSearch: string) => void;
}

interface TableProps extends TableState, TableDispatch {}

/**
 * This table is divided in three tables: one on the left to have sticky columns on common properties (common.tsx)
 * On the second table, you will have the additional properties of the records (details.tsx)
 * On the thrid one, you have all the actions of the record.
 */
export default class Table extends React.Component<TableProps, {nextItemToAddPosition: number}> {
  private timer: undefined | number;
  private needResize = false;
  readonly state = {
    nextItemToAddPosition: 0,
  };

  componentWillReceiveProps(nextProps: TableProps) {
    if (this.props.grid.records.length !== nextProps.grid.records.length) {
      this.setState({nextItemToAddPosition: this.props.grid.records.length});
    }
  }

  componentDidMount() {
    const detailTable = this.refs.detailTable as any;
    const verticalScrollContainer = this.refs.verticalScrollContainer as any;
    if (
      undefined !== detailTable &&
      undefined !== verticalScrollContainer &&
      detailTable.offsetWidth !== verticalScrollContainer.offsetWidth
    ) {
      this.needResize = true;
      window.addEventListener('resize', this.resizeScrollContainer.bind(this));
    }
  }

  componentDidUpdate() {
    if (this.needResize) {
      this.resizeScrollContainer();
    }
    const horizontalScrollContainer = this.refs.horizontalScrollContainer as any;
    if (this.props.grid.page === 0 && undefined !== horizontalScrollContainer) {
      horizontalScrollContainer.scrollTop = 0;
    }
  }

  componentDidUnMount() {
    if (this.needResize) {
      window.removeEventListener('resize', this.resizeScrollContainer.bind(this));
    }
  }

  resizeScrollContainer() {
    const verticalScrollContainer = this.refs.verticalScrollContainer as any;
    const horizontalScrollContainer = this.refs.horizontalScrollContainer as any;
    const commonTable = this.refs.commonTable as any;
    const detailTable = this.refs.detailTable as any;
    const actionTable = this.refs.actionTable as any;
    if (
      undefined !== verticalScrollContainer &&
      undefined !== horizontalScrollContainer &&
      undefined !== commonTable &&
      undefined !== detailTable &&
      undefined !== actionTable
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
  }

  handleScroll() {
    const verticalScrollContainer = this.refs.verticalScrollContainer as any;
    const horizontalScrollContainer = this.refs.horizontalScrollContainer as any;
    const scrollSize = verticalScrollContainer.offsetHeight;
    const scrollPosition = horizontalScrollContainer.scrollTop;
    const containerSize = horizontalScrollContainer.offsetHeight;
    const remainingHeightToBottom = scrollSize - scrollPosition - containerSize;
    if (remainingHeightToBottom < 5 * containerSize) {
      this.props.onNeedMoreResults();
    }
  }

  /**
   * This method is triggered each time the user types on the search field
   * It dispatches events only if the user pauses for more than 100ms
   */
  onSearchUpdated(event: React.ChangeEvent<HTMLInputElement>) {
    const userSearch = event.currentTarget.value;
    if (undefined !== this.timer) {
      clearTimeout(this.timer);
    }
    this.timer = setTimeout(() => {
      this.props.onSearchUpdated(userSearch);
    }, 100) as any;
  }

  renderItems(
    records: NormalizedRecord[],
    locale: string,
    isLoading: boolean,
    onRedirectToRecord: (record: NormalizedRecord) => void,
    onDeleteRecord: (recordCode: RecordCode, label: string) => void,
    View: RowView,
    columns: Column[],
    cellViews: CellViews,
    recordCount: number
  ): JSX.Element[] {
    if (0 === records.length && isLoading) {
      const record = {
        identifier: '',
        reference_entity_identifier: '',
        code: '',
        labels: {},
        image: null,
        values: [],
      };

      const placeholderCount = recordCount < 30 ? recordCount : 30;

      return Array.from(Array(placeholderCount).keys()).map(key => (
        <View
          isLoading={isLoading}
          key={key}
          record={record}
          locale={locale}
          onRedirectToRecord={() => {}}
          onDeleteRecord={() => {}}
          position={key}
          columns={columns}
          cellViews={cellViews}
        />
      ));
    }

    return records.map((record: NormalizedRecord, index: number) => {
      const itemPosition = index - this.state.nextItemToAddPosition;

      return (
        <View
          isLoading={false}
          key={record.identifier}
          record={record}
          locale={locale}
          onRedirectToRecord={onRedirectToRecord}
          onDeleteRecord={onDeleteRecord}
          position={itemPosition > 0 ? itemPosition : 0}
          columns={columns}
          cellViews={cellViews}
        />
      );
    });
  }

  render(): JSX.Element | JSX.Element[] {
    const {grid, locale, channel, onRedirectToRecord, onDeleteRecord, recordCount, cellViews} = this.props;
    const columnsToDisplay = grid.columns.filter(
      (column: Column) => column.channel === channel && column.locale === locale
    );

    return (
      <React.Fragment>
        <div className="AknFilterBox-searchContainer">
          <input
            type="text"
            className="AknFilterBox-search"
            placeholder={__('pim_reference_entity.record.grid.search')}
            onChange={this.onSearchUpdated.bind(this)}
          />
        </div>
        {0 === grid.records.length && false === grid.isLoading ? (
          <div className="AknGridContainer-noData">
            <div className="AknGridContainer-noDataImage" />
            <div className="AknGridContainer-noDataTitle">
              {__('pim_reference_entity.record.no_result.title', {
                entityLabel: this.props.referenceEntity.getLabel(locale),
              })}
            </div>
            <div className="AknGridContainer-noDataSubtitle">
              {__('pim_reference_entity.record.no_result.subtitle')}
            </div>
          </div>
        ) : (
          <div
            className="AknDefault-horizontalScrollContainer"
            onScroll={this.handleScroll.bind(this)}
            ref="horizontalScrollContainer"
          >
            <div className="AknDefault-verticalScrollContainer" ref="verticalScrollContainer">
              <table className="AknGrid AknGrid--light AknGrid--left" ref="commonTable">
                <thead className="AknGrid-header">
                  <tr className="AknGrid-bodyRow">
                    <th className="AknGrid-headerCell">{__('pim_reference_entity.record.grid.column.image')}</th>
                    <th className="AknGrid-headerCell">{__('pim_reference_entity.record.grid.column.label')}</th>
                    <th className="AknGrid-headerCell">{__('pim_reference_entity.record.grid.column.code')}</th>
                  </tr>
                </thead>
                <tbody className="AknGrid-body">
                  {this.renderItems(
                    grid.records,
                    locale,
                    grid.isLoading,
                    onRedirectToRecord,
                    onDeleteRecord,
                    CommonView,
                    [],
                    {},
                    recordCount
                  )}
                </tbody>
              </table>
              <table
                className="AknGrid AknGrid--light AknGrid--center"
                style={{flex: 1}}
                ref="detailTable"
              >
                <thead className="AknGrid-header">
                  <tr className="AknGrid-bodyRow">
                    {0 === columnsToDisplay.length ? (
                      <th className="AknGrid-headerCell" />
                    ) : (
                      columnsToDisplay.map((column: Column) => {
                        return (
                          <th key={column.key} className="AknGrid-headerCell">
                            {getLabel(column.labels, locale, column.code)}
                          </th>
                        );
                      })
                    )}
                  </tr>
                </thead>
                <tbody className="AknGrid-body">
                  {this.renderItems(
                    grid.records,
                    locale,
                    grid.isLoading,
                    onRedirectToRecord,
                    onDeleteRecord,
                    DetailsView,
                    columnsToDisplay,
                    cellViews,
                    recordCount
                  )}
                </tbody>
              </table>
              <table className="AknGrid AknGrid--light AknGrid--right" ref="actionTable">
                <thead className="AknGrid-header">
                  <tr className="AknGrid-bodyRow">
                    <th className="AknGrid-headerCell AknGrid-headerCell--action" />
                  </tr>
                </thead>
                <tbody className="AknGrid-body">
                  {this.renderItems(
                    grid.records,
                    locale,
                    grid.isLoading,
                    onRedirectToRecord,
                    onDeleteRecord,
                    ActionView,
                    [],
                    {},
                    recordCount
                  )}
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
                  {__('pim_reference_entity.record.grid.more_result.title')}
                  <div className="AknDescriptionHeader-description">
                    {__('pim_reference_entity.record.grid.more_result.description', {total: grid.total})}
                  </div>
                </div>
              </div>
            ) : null}
          </div>
        )}
      </React.Fragment>
    );
  }
}
