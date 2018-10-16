import CommonView from 'akeneoreferenceentity/application/component/record/index/common';
import ActionView from 'akeneoreferenceentity/application/component/record/index/action';
import DetailsView from 'akeneoreferenceentity/application/component/record/index/details';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import * as React from 'react';
import denormalizeRecord from 'akeneoreferenceentity/application/denormalizer/record';
import __ from 'akeneoreferenceentity/tools/translator';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';

interface TableState {
  locale: string;
  channel: string;
  columns: Column[];
  referenceEntity: ReferenceEntity;
  records: NormalizedRecord[];
  isLoading: boolean;
}

interface TableDispatch {
  onRedirectToRecord: (record: NormalizedRecord) => void;
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
  readonly state = {
    nextItemToAddPosition: 0,
  };

  componentWillReceiveProps(nextProps: TableProps) {
    if (this.props.records.length !== nextProps.records.length) {
      this.setState({nextItemToAddPosition: this.props.records.length});
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
      verticalScrollContainer.style.width = `${commonTable.offsetWidth +
        detailTable.offsetWidth +
        actionTable.offsetWidth}px`;
      verticalScrollContainer.style.minWidth = `${horizontalScrollContainer.offsetWidth}px`;
    }
  }

  componentDidMount() {
    window.addEventListener('resize', this.resizeScrollContainer.bind(this));
  }

  componentDidUpdate() {
    this.resizeScrollContainer();
  }

  componentDidUnMount() {
    window.removeEventListener('resize', this.resizeScrollContainer.bind(this));
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

  renderItems(
    records: NormalizedRecord[],
    locale: string,
    isLoading: boolean,
    onRedirectToRecord: (record: NormalizedRecord) => void,
    View: any, //TODO: fix
    columns?: Column[]
  ): JSX.Element | JSX.Element[] {
    if (0 === records.length && isLoading) {
      const record = denormalizeRecord({
        identifier: '',
        reference_entity_identifier: '',
        code: '',
        labels: {},
        image: null,
        values: [],
      });

      return (
        <View
          isLoading={isLoading}
          key={0}
          record={record}
          locale={locale}
          onRedirectToRecord={() => {}}
          position={0}
        />
      );
    }

    return records.map((record: NormalizedRecord, index: number) => {
      const itemPosition = index - this.state.nextItemToAddPosition;

      return (
        <View
          key={record.identifier}
          record={record}
          locale={locale}
          onRedirectToRecord={onRedirectToRecord}
          position={itemPosition > 0 ? itemPosition : 0}
          columns={columns}
        />
      );
    });
  }

  onSearchUpdated(event: React.ChangeEvent<HTMLInputElement>) {
    const userSearch = event.currentTarget.value;
    if (undefined !== this.timer) {
      clearTimeout(this.timer);
    }
    this.timer = setTimeout(() => {
      this.props.onSearchUpdated(userSearch);
    }, 100) as any;
  }

  render(): JSX.Element | JSX.Element[] {
    const {records, locale, channel, columns, onRedirectToRecord, isLoading} = this.props;
    const columnsToDisplay = columns.filter((column: Column) => column.channel === channel && column.locale === locale);

    return (
      <React.Fragment>
        <div className="AknFilterBox-searchContainer">
          <input type="text" className="AknFilterBox-search" onChange={this.onSearchUpdated.bind(this)} />
        </div>
        {0 !== records.length ? (
          <div
            className="AknDefault-horizontalScrollContainer"
            onScroll={this.handleScroll.bind(this)}
            ref="horizontalScrollContainer"
          >
            <div className="AknDefault-verticalScrollContainer" ref="verticalScrollContainer">
              <table className="AknGrid AknGrid--light AknGrid--left" style={{flex: '3'}} ref="commonTable">
                <thead className="AknGrid-header">
                  <tr className="AknGrid-bodyRow">
                    <th className="AknGrid-headerCell">{__('pim_reference_entity.record.grid.column.image')}</th>
                    <th className="AknGrid-headerCell">{__('pim_reference_entity.record.grid.column.code')}</th>
                    <th className="AknGrid-headerCell">{__('pim_reference_entity.record.grid.column.label')}</th>
                  </tr>
                </thead>
                <tbody className="AknGrid-body">
                  {this.renderItems(records, locale, isLoading, onRedirectToRecord, CommonView)}
                </tbody>
              </table>
              <table
                className="AknGrid AknGrid--light AknGrid--center"
                style={{flex: columns.length}}
                ref="detailTable"
              >
                <thead className="AknGrid-header">
                  <tr className="AknGrid-bodyRow">
                    {columnsToDisplay.map((column: Column) => {
                      return (
                        <th key={column.key} className="AknGrid-headerCell">
                          {column.labels[locale]}
                        </th>
                      );
                    })}
                  </tr>
                </thead>
                <tbody className="AknGrid-body">
                  {this.renderItems(records, locale, isLoading, onRedirectToRecord, DetailsView, columnsToDisplay)}
                </tbody>
              </table>
              <table className="AknGrid AknGrid--light AknGrid--right" style={{flex: '1'}} ref="actionTable">
                <thead className="AknGrid-header">
                  <tr className="AknGrid-bodyRow">
                    <th className="AknGrid-headerCell AknGrid-headerCell--action" />
                  </tr>
                </thead>
                <tbody className="AknGrid-body">
                  {this.renderItems(records, locale, isLoading, onRedirectToRecord, ActionView)}
                </tbody>
              </table>
            </div>
          </div>
        ) : (
          <div className="AknGridContainer-noData">
            <div className="AknGridContainer-noDataImage" />
            <div className="AknGridContainer-noDataTitle">
              {__('pim_reference_entity.record.no_data.title', {
                entityLabel: this.props.referenceEntity.getLabel(locale),
              })}
            </div>
            <div className="AknGridContainer-noDataSubtitle">{__('pim_reference_entity.record.no_data.subtitle')}</div>
          </div>
        )}
      </React.Fragment>
    );
  }
}
