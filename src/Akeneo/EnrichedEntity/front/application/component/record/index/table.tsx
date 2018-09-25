import ItemView from 'akeneoenrichedentity/application/component/record/index/item';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import * as React from 'react';
import denormalizeRecord from 'akeneoenrichedentity/application/denormalizer/record';

interface TableState {
  locale: string;
  records: Record[];
  isLoading: boolean;
}

interface TableDispatch {
  onRedirectToRecord: (record: Record) => void;
}

interface TableProps extends TableState, TableDispatch {}

export default class Table extends React.Component<TableProps, {nextItemToAddPosition: number}> {
  readonly state = {
    nextItemToAddPosition: 0,
  };

  componentWillReceiveProps(nextProps: TableProps) {
    if (this.props.records.length !== nextProps.records.length) {
      this.setState({nextItemToAddPosition: this.props.records.length});
    }
  }

  renderItems(
    records: Record[],
    locale: string,
    isLoading: boolean,
    onRedirectToRecord: (record: Record) => void
  ): JSX.Element | JSX.Element[] {
    if (0 === records.length && isLoading) {
      const record = denormalizeRecord({
        identifier: '',
        enriched_entity_identifier: '',
        code: '',
        labels: {},
        image: null,
        values: [],
      });

      return (
        <ItemView
          isLoading={isLoading}
          key={0}
          record={record}
          locale={locale}
          onRedirectToRecord={() => {}}
          position={0}
        />
      );
    }

    return records.map((record: Record, index: number) => {
      const itemPosition = index - this.state.nextItemToAddPosition;

      return (
        <ItemView
          key={record.getIdentifier().identifier}
          record={record}
          locale={locale}
          onRedirectToRecord={onRedirectToRecord}
          position={itemPosition > 0 ? itemPosition : 0}
        />
      );
    });
  }

  render(): JSX.Element | JSX.Element[] {
    const {records, locale, onRedirectToRecord, isLoading} = this.props;

    return (
      <table className="AknGrid">
        <tbody className="AknGrid-body">{this.renderItems(records, locale, isLoading, onRedirectToRecord)}</tbody>
      </table>
    );
  }
}
