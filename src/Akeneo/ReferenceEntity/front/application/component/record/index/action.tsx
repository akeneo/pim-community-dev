import * as React from 'react';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import {getLabel} from 'pimui/js/i18n';
const router = require('pim/router');

const memo = (React as any).memo;

const ActionRow = memo(
  ({
    record,
    placeholder = false,
    locale,
    onRedirectToRecord,
    onDeleteRecord,
  }: {
    record: NormalizedRecord;
    locale: string;
    placeholder?: boolean;
  } & {
    onRedirectToRecord: (record: NormalizedRecord) => void;
    onDeleteRecord: (recordCode: RecordCode, label: string) => void;
  }) => {
    if (true === placeholder) {
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
        className={`AknGrid-bodyRow AknGrid-bodyRow--withoutTopBorder ${placeholder ? 'AknLoadingPlaceHolder' : ''}`}
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

const ActionRows = memo(
  ({
    records,
    locale,
    placeholder,
    onRedirectToRecord,
    onDeleteRecord,
    recordCount,
  }: {
    records: NormalizedRecord[];
    locale: string;
    placeholder: boolean;
    onRedirectToRecord: (record: NormalizedRecord) => void;
    onDeleteRecord: (recordCode: RecordCode, label: string) => void;
    recordCount: number;
  }) => {
    if (placeholder) {
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
        <ActionRow
          placeholder={true}
          key={key}
          record={record}
          locale={locale}
          onRedirectToRecord={() => {}}
          onDeleteRecord={() => {}}
        />
      ));
    }

    return records.map((record: NormalizedRecord) => {
      return (
        <ActionRow
          placeholder={false}
          key={record.identifier}
          record={record}
          locale={locale}
          onRedirectToRecord={onRedirectToRecord}
          onDeleteRecord={onDeleteRecord}
        />
      );
    });
  }
);

export default ActionRows;
