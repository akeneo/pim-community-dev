import * as React from 'react';
import {connect} from 'react-redux';
import Table from 'akeneoreferenceentity/application/component/record/index/table';
import Record from 'akeneoreferenceentity/domain/model/record/record';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {redirectToRecord} from 'akeneoreferenceentity/application/action/record/router';
import __ from 'akeneoreferenceentity/tools/translator';
import ReferenceEntity, {
  denormalizeReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import Header from 'akeneoreferenceentity/application/component/reference-entity/edit/header';
import {recordCreationStart} from 'akeneoreferenceentity/domain/event/record/create';
import {deleteReferenceEntity} from 'akeneoreferenceentity/application/action/reference-entity/edit';
import {
  SecondaryAction,
  breadcrumbConfiguration,
} from 'akeneoreferenceentity/application/component/reference-entity/edit';
const securityContext = require('pim/security-context');

interface StateProps {
  context: {
    locale: string;
  };
  referenceEntity: ReferenceEntity;
  grid: {
    records: Record[];
    total: number;
    isLoading: boolean;
  };
  acls: {
    createRecord: boolean;
    delete: boolean;
  };
}

interface DispatchProps {
  events: {
    onRedirectToRecord: (record: Record) => void;
    onDelete: (referenceEntity: ReferenceEntity) => void;
    onRecordCreationStart: () => void;
  };
}

const records = ({context, grid, events, referenceEntity, acls}: StateProps & DispatchProps) => {
  return (
    <React.Fragment>
      <Header
        label={referenceEntity.getLabel(context.locale)}
        image={referenceEntity.getImage()}
        primaryAction={() => {
          return acls.createRecord ? (
            <button className="AknButton AknButton--action" onClick={events.onRecordCreationStart}>
              {__('pim_reference_entity.record.button.create')}
            </button>
          ) : null;
        }}
        secondaryActions={() => {
          return acls.delete ? (
            <SecondaryAction
              onDelete={() => {
                events.onDelete(referenceEntity);
              }}
            />
          ) : null;
        }}
        withLocaleSwitcher={true}
        withChannelSwitcher={true}
        isDirty={false}
        breadcrumbConfiguration={breadcrumbConfiguration}
      />
      {0 !== grid.records.length ? (
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
            {__('pim_reference_entity.record.no_data.title', {
              entityLabel: referenceEntity.getLabel(context.locale),
            })}
          </div>
          <div className="AknGridContainer-noDataSubtitle">{__('pim_reference_entity.record.no_data.subtitle')}</div>
        </div>
      )}
    </React.Fragment>
  );
};

export default connect(
  (state: EditState): StateProps => {
    const referenceEntity = denormalizeReferenceEntity(state.form.data);
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;
    const records = undefined === state.grid || undefined === state.grid.items ? [] : state.grid.items;
    const total = undefined === state.grid || undefined === state.grid.total ? 0 : state.grid.total;

    return {
      context: {
        locale,
      },
      referenceEntity,
      grid: {
        records,
        total,
        isLoading: state.grid.isFetching && state.grid.items.length === 0,
      },
      acls: {
        createRecord: securityContext.isGranted('akeneo_referenceentity_record_create'),
        delete: securityContext.isGranted('akeneo_referenceentity_reference_entity_delete'),
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onRedirectToRecord: (record: Record) => {
          dispatch(redirectToRecord(record));
        },
        onRecordCreationStart: () => {
          dispatch(recordCreationStart());
        },
        onDelete: (referenceEntity: ReferenceEntity) => {
          dispatch(deleteReferenceEntity(referenceEntity));
        },
      },
    };
  }
)(records);
