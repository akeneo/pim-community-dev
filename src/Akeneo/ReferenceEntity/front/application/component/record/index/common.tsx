import * as React from 'react';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
import {denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import {getLabel} from 'pimenrich/js/i18n';
import {RowView} from 'akeneoreferenceentity/application/component/record/index/table';

const CommonView: RowView = ({
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
  if (true === isLoading) {
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
      <td className="AknGrid-bodyCell" title={label}>{label}</td>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--identifier" title={record.code}>{record.code}</td>
    </tr>
  );
};

export default CommonView;
