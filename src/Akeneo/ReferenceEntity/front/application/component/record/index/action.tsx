import * as React from 'react';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {RowView} from 'akeneoreferenceentity/application/component/record/index/table';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import {getLabel} from 'pimui/js/i18n';
const router = require('pim/router');

const ActionView: RowView = React.memo(
  ({
    record,
    isLoading = false,
    locale,
    onRedirectToRecord,
    onDeleteRecord,
  }: {
    record: NormalizedRecord;
    locale: string;
    isLoading?: boolean;
    position: number;
  } & {
    onRedirectToRecord: (record: NormalizedRecord) => void;
    onDeleteRecord: (recordCode: RecordCode, label: string) => void;
  }) => {
    if (true === isLoading) {
      return (
        <tr>
          <td className="AknGrid-bodyCell">
            <div className="AknGrid-bodyCellContainer AknLoadingPlaceHolder" />
          </td>
        </tr>
      );
    }

    const path =
      '' !== record.identifier
        ? `#${router.generate('akeneo_reference_entities_record_edit', {
            referenceEntityIdentifier: record.reference_entity_identifier,
            recordCode: record.code,
            tab: 'enrich',
          })}`
        : '';

    const label = getLabel(record.labels, locale, record.code);

    return (
      <tr
        className={`AknGrid-bodyRow AknGrid-bodyRow--withoutTopBorder ${isLoading ? 'AknLoadingPlaceHolder' : ''}`}
        data-identifier={record.identifier}
      >
        <td className="AknGrid-bodyCell AknGrid-bodyCell--action">
          <div className="AknButtonList AknButtonList--right">
            <span
              tabIndex={0}
              onKeyPress={(event: React.KeyboardEvent<HTMLAnchorElement>) => {
                event.preventDefault();

                onDeleteRecord(RecordCode.create(record.code), label);

                return false;
              }}
              className="AknIconButton AknIconButton--small AknIconButton--trash AknButtonList-item"
              data-identifier={record.identifier}
              onClick={event => {
                event.preventDefault();

                onDeleteRecord(RecordCode.create(record.code), label);

                return false;
              }}
            />
            <a
              tabIndex={0}
              href={path}
              onKeyPress={(event: React.KeyboardEvent<HTMLAnchorElement>) => {
                event.preventDefault();
                if (' ' === event.key) {
                  onRedirectToRecord(record);
                }

                return false;
              }}
              className="AknIconButton AknIconButton--small AknIconButton--edit AknButtonList-item"
              data-identifier={record.identifier}
              onClick={event => {
                event.preventDefault();

                onRedirectToRecord(record);

                return false;
              }}
            />
          </div>
        </td>
      </tr>
    );
  }
);

export default ActionView;
