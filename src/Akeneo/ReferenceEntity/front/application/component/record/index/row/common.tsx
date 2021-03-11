import React from 'react';
import {NormalizedItemRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
import {denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import {getLabel} from 'pimui/js/i18n';
import Completeness from 'akeneoreferenceentity/domain/model/record/completeness';
import {CompletenessBadge} from 'akeneoreferenceentity/application/component/app/completeness';

const memo = (React as any).memo;

const CommonRow = memo(
  ({
    record,
    locale,
    placeholder = false,
    onRedirectToRecord,
  }: {
    record: NormalizedItemRecord;
    locale: string;
    placeholder?: boolean;
  } & {
    onRedirectToRecord: (record: NormalizedItemRecord) => void;
  }) => {
    if (true === placeholder) {
      return (
        <tr>
          <td className="AknGrid-bodyCell AknGrid-bodyCell--image">
            <div className="AknGrid-bodyCellContainer AknLoadingPlaceHolder" />
          </td>
          <td className="AknGrid-bodyCell" colSpan={3}>
            <div className="AknGrid-bodyCellContainer AknLoadingPlaceHolder" />
          </td>
        </tr>
      );
    }

    const label = getLabel(record.labels, locale, record.code);

    return (
      <tr
        className="AknGrid-bodyRow"
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
        <td className="AknGrid-bodyCell">
          <CompletenessBadge completeness={Completeness.createFromNormalized(record.completeness)} />
        </td>
      </tr>
    );
  }
);

const CommonRows = memo(
  ({
    records,
    locale,
    placeholder,
    onRedirectToRecord,
    recordCount,
  }: {
    records: NormalizedItemRecord[];
    locale: string;
    placeholder: boolean;
    onRedirectToRecord: (record: NormalizedItemRecord) => void;
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
        completeness: {},
      };

      const placeholderCount = recordCount < 30 ? recordCount : 30;

      return Array.from(Array(placeholderCount).keys()).map(key => (
        <CommonRow placeholder={placeholder} key={key} record={record} locale={locale} onRedirectToRecord={() => {}} />
      ));
    }

    return records.map((record: NormalizedItemRecord) => {
      return (
        <CommonRow
          placeholder={false}
          key={record.identifier}
          record={record}
          locale={locale}
          onRedirectToRecord={onRedirectToRecord}
        />
      );
    });
  }
);

export default CommonRows;
