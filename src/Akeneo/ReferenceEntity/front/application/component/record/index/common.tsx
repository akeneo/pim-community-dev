import * as React from 'react';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
import {denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import {getLabel} from 'pimenrich/js/i18n';

export default ({
  record,
  locale,
  isLoading = false,
  onRedirectToRecord,
}: {
  record: NormalizedRecord;
  locale: string;
  isLoading?: boolean;
  position: number;
} & {
  onRedirectToRecord: (record: NormalizedRecord) => void;
}) => {
  const label = getLabel(record.labels, locale, record.code);

  return (
    <tr
      className={`AknGrid-bodyRow AknGrid-bodyRow--withoutTopBorder ${isLoading ? 'AknLoadingPlaceHolder' : ''}`}
      data-identifier={record.identifier}
      onClick={event => {
        event.preventDefault();

        onRedirectToRecord(record);

        return false;
      }}
    >
      <td className="AknGrid-bodyCell AknGrid-bodyCell--image">
        <img className="AknGrid-image" src={getImageShowUrl(denormalizeFile(record.image), 'thumbnail_small')} />
      </td>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--identifier">{record.code}</td>
      <td className="AknGrid-bodyCell">{label}</td>
    </tr>
  );
};
