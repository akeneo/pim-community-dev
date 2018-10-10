import CommonView from 'akeneoreferenceentity/application/component/record/index/common';
import ActionView from 'akeneoreferenceentity/application/component/record/index/action';
import DetailsView from 'akeneoreferenceentity/application/component/record/index/details';
import Record, {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import * as React from 'react';
import denormalizeRecord from 'akeneoreferenceentity/application/denormalizer/record';
import __ from 'akeneoreferenceentity/tools/translator';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';

interface TableState {
  locale: string;
  referenceEntity: ReferenceEntity,
  records: NormalizedRecord[];
  isLoading: boolean;
}

interface TableDispatch {
  onRedirectToRecord: (record: Record) => void;
  onNeedMoreResults: () => void;
  onSearchUpdated: (userSearch: string) => void;
}

interface TableProps extends TableState, TableDispatch {}

export default class Table extends React.Component<TableProps, {nextItemToAddPosition: number}> {
  private timer: undefined|number;
  readonly state = {
    nextItemToAddPosition: 0,
  };

  componentWillReceiveProps(nextProps: TableProps) {
    if (this.props.records.length !== nextProps.records.length) {
      this.setState({nextItemToAddPosition: this.props.records.length});
    }
  }

  handleScroll() {
    const verticalScrollContainer = this.refs.verticalScrollContainer as any;
    const horizontalScrollContainer = this.refs.horizontalScrollContainer as any;
    const scrollSize = verticalScrollContainer.offsetHeight;
    const scrollPosition = horizontalScrollContainer.scrollTop;
    const containerSize = horizontalScrollContainer.offsetHeight;
    const remainingHeightToBottom = scrollSize - scrollPosition - containerSize;
    if (remainingHeightToBottom < 2000) {
      this.props.onNeedMoreResults();
    }
  }

  renderItems(
    records: NormalizedRecord[],
    locale: string,
    isLoading: boolean,
    onRedirectToRecord: (record: Record) => void,
    View: any
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
    const {records, locale, onRedirectToRecord, isLoading} = this.props;

    return (
      <div className="AknDefault-horizontalScrollContainer" onScroll={this.handleScroll.bind(this)} ref="horizontalScrollContainer">
        <div className="AknFilterBox-searchContainer">
          <input type="text" className="AknFilterBox-search" onChange={this.onSearchUpdated.bind(this)}/>
        </div>
        {0 !== records.length ? (
          <div className="AknDefault-verticalScrollContainer" ref="verticalScrollContainer">
            <table className="AknGrid AknGrid--light AknGrid--left" style={{flex: '2'}}>
              <thead className="AknGrid-header">
                <tr className="AknGrid-bodyRow">
                  <th className="AknGrid-headerCell">{__('pim_reference_entity.record.grid.column.image')}</th>
                  <th className="AknGrid-headerCell">{__('pim_reference_entity.record.grid.column.code')}</th>
                </tr>
              </thead>
              <tbody className="AknGrid-body">
                {this.renderItems(records, locale, isLoading, onRedirectToRecord, CommonView)}
              </tbody>
            </table>
            <table className="AknGrid AknGrid--light AknGrid--center" style={{flex: '9'}}>
              <thead className="AknGrid-header">
                <tr className="AknGrid-bodyRow">
                  <th className="AknGrid-headerCell">{__('pim_reference_entity.record.grid.column.label')}</th>
                </tr>
              </thead>
              <tbody className="AknGrid-body">
                {this.renderItems(records, locale, isLoading, onRedirectToRecord, DetailsView)}
              </tbody>
            </table>
            <table className="AknGrid AknGrid--light AknGrid--right" style={{flex: '1'}}>
              <thead className="AknGrid-header">
                <tr className="AknGrid-bodyRow">
                  <th className="AknGrid-headerCell AknGrid-headerCell--action"></th>
                </tr>
              </thead>
              <tbody className="AknGrid-body">
                {this.renderItems(records, locale, isLoading, onRedirectToRecord, ActionView)}
              </tbody>
            </table>
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
      </div>
    );
  }
}
