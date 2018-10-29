import * as React from 'react';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
import {denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import {getLabel} from 'pimui/js/i18n';

const CommonRow = React.memo(
  ({
    record,
    locale,
    placeholder = false,
    onRedirectToRecord,
  }: {
    record: NormalizedRecord;
    locale: string;
    placeholder?: boolean;
    position: number;
  } & {
    onRedirectToRecord: (record: NormalizedRecord) => void;
  }) => {
    if (true === placeholder) {
      return (
        <tr>
          <td className="AknGrid-bodyCell AknGrid-bodyCell--image">
            <div className="AknGrid-bodyCellContainer AknLoadingPlaceHolder" />
          </td>
          <td className="AknGrid-bodyCell" colSpan={2}>
            <div className="AknGrid-bodyCellContainer AknLoadingPlaceHolder" />
          </td>
        </tr>
      );
    }

    const label = getLabel(record.labels, locale, record.code);

    return (
      <tr
        className="AknGrid-bodyRow AknGrid-bodyRow--withoutTopBorder"
        data-identifier={record.identifier}
        onClick={event => {
          event.preventDefault();

          onRedirectToRecord(record);

          return false;
        }}
      >
        <td className="AknGrid-bodyCell AknGrid-bodyCell--image">
          <img
            className="AknGrid-image AknLoadingPlaceHolder"
            width="44"
            height="44"
            src={getImageShowUrl(denormalizeFile(record.image), 'thumbnail_small')}
          />
        </td>
        <td className="AknGrid-bodyCell" title={label}>
          {label}
        </td>
        <td className="AknGrid-bodyCell AknGrid-bodyCell--identifier" title={record.code}>
          {record.code}
        </td>
      </tr>
    );
  }
);

const CommonRows = React.memo(
  ({
    records,
    locale,
    placeholder,
    onRedirectToRecord,
    nextItemToAddPosition,
    recordCount,
  }: {
    records: NormalizedRecord[];
    locale: string;
    placeholder: boolean;
    onRedirectToRecord: (record: NormalizedRecord) => void;
    nextItemToAddPosition: number;
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
        <CommonRow
          placeholder={placeholder}
          key={key}
          record={record}
          locale={locale}
          onRedirectToRecord={() => {}}
          position={key}
        />
      ));
    }

    return records.map((record: NormalizedRecord, index: number) => {
      const itemPosition = index - nextItemToAddPosition;

      return (
        <CommonRow
          placeholder={false}
          key={record.identifier}
          record={record}
          locale={locale}
          onRedirectToRecord={onRedirectToRecord}
          position={itemPosition > 0 ? itemPosition : 0}
        />
      );
    });
  }
);

export default CommonRows;
