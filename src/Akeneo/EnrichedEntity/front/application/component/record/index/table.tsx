import * as React from 'react';
import ItemView from 'akeneoenrichedentity/application/component/record/index/item';
import Record, {createRecord} from 'akeneoenrichedentity/domain/model/record/record';
import {createIdentifier} from 'akeneoenrichedentity/domain/model/record/identifier';
import {createIdentifier as createEnrichedEntityIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';


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
      const recordIdentifier = createIdentifier('');
      const enrichedEntityIdentifier = createEnrichedEntityIdentifier('');
      const labelCollection = createLabelCollection({});
      const record = createRecord(recordIdentifier, enrichedEntityIdentifier, labelCollection);

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
          key={record.getIdentifier().stringValue()}
          record={record}
          locale={locale}
          onRedirectToRecord={onRedirectToRecord}
          position={itemPosition > 0 ? itemPosition : 0}
        />
      );
    })
  }

  render(): JSX.Element | JSX.Element[] {
    const {
      records,
      locale,
      onRedirectToRecord,
      isLoading
    } = this.props;

    return (
      <table className="AknGrid">
        <tbody className="AknGrid-body">
          {this.renderItems(records, locale, isLoading, onRedirectToRecord)}
        </tbody>
      </table>
    );
  }
}
