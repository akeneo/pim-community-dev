import * as React from 'react';
import {connect} from 'react-redux';
import Table from 'akeneoenrichedentity/application/component/record/index/table';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import {redirectToRecord} from 'akeneoenrichedentity/application/action/record/router';
import __ from 'akeneoenrichedentity/tools/translator';
import EnrichedEntity, {
  denormalizeEnrichedEntity,
} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';

interface StateProps {
  context: {
    locale: string;
  };
  enrichedEntity: EnrichedEntity;
  grid: {
    records: Record[];
    total: number;
    isLoading: boolean;
  };
}

interface DispatchProps {
  events: {
    onRedirectToRecord: (record: Record) => void;
  };
}

const records = ({context, grid, events, enrichedEntity}: StateProps & DispatchProps) => {
  return 0 !== grid.records.length ? (
    <Table
      onRedirectToRecord={events.onRedirectToRecord}
      locale={context.locale}
      records={grid.records}
      isLoading={grid.isLoading}
    />
  ) : (
    <div className="AknGridContainer-noData">
      <div className="AknGridContainer-noDataImage" />
      <div className="AknGridContainer-noDataTitle">
        {__('pim_enriched_entity.record.no_data.title', {
          entityLabel: enrichedEntity.getLabel(context.locale),
        })}
      </div>
      <div className="AknGridContainer-noDataSubtitle">{__('pim_enriched_entity.record.no_data.subtitle')}</div>
    </div>
  );
};

export default connect(
  (state: EditState): StateProps => {
    const enrichedEntity = denormalizeEnrichedEntity(state.form.data);
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;
    const records = undefined === state.grid || undefined === state.grid.items ? [] : state.grid.items;
    const total = undefined === state.grid || undefined === state.grid.total ? 0 : state.grid.total;

    return {
      context: {
        locale,
      },
      enrichedEntity,
      grid: {
        records,
        total,
        isLoading: state.grid.isFetching && state.grid.items.length === 0,
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onRedirectToRecord: (record: Record) => {
          dispatch(redirectToRecord(record));
        },
      },
    };
  }
)(records);
