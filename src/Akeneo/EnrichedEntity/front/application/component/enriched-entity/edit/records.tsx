import * as React from 'react';
import {connect} from 'react-redux';
import Table from 'akeneoenrichedentity/application/component/record/index/table';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import {State} from 'akeneoenrichedentity/application/reducer/record/index';
import {redirectToRecord} from 'akeneoenrichedentity/application/action/record/router';

interface StateProps {
  context: {
    locale: string;
  };

  grid: {
    records: Record[];
    total: number;
    isLoading: boolean;
  };
};

interface DispatchProps {
  events: {
    onRedirectToRecord: (record: Record) => void
  }
}

const records = ({context, grid, events}: StateProps & DispatchProps) => {
  return(
    <Table
      onRedirectToRecord={events.onRedirectToRecord}
      locale={context.locale}
      records={grid.records}
      isLoading={grid.isLoading}
    />
  );
}

export default connect((state: State): StateProps => {
  const locale = undefined === state.user || undefined === state.user.uiLocale ? '' : state.user.uiLocale;
  const records = undefined === state.grid || undefined === state.grid.items ? [] : state.grid.items;
  const total = undefined === state.grid || undefined === state.grid.total ? 0 : state.grid.total;

  return {
    context: {
      locale
    },
    grid: {
      records,
      total,
      isLoading: state.grid.isFetching && state.grid.items.length === 0
    }
  }
}, (dispatch: any): DispatchProps => {
  return {
    events: {
      onRedirectToRecord: (record: Record) => {
        dispatch(redirectToRecord(record));
      }
    }
  }
})(records);
