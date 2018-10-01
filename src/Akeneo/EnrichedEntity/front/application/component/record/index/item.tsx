import * as React from 'react';
import Record from 'akeneoreferenceentity/domain/model/record/record';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
const router = require('pim/router');

export default ({
  record,
  locale,
  isLoading = false,
  onRedirectToRecord,
}: {
  record: Record;
  locale: string;
  isLoading?: boolean;
  position: number;
} & {
  onRedirectToRecord: (record: Record) => void;
}) => {
  const path =
    '' !== record.getIdentifier().identifier
      ? `#${router.generate('akeneo_reference_entities_record_edit', {
          referenceEntityIdentifier: record.getReferenceEntityIdentifier().stringValue(),
          recordCode: record.getCode().stringValue(),
          tab: 'enrich',
        })}`
      : '';

  return (
    <tr
      className={`AknGrid-bodyRow AknGrid-bodyRow--withoutTopBorder ${isLoading ? 'AknLoadingPlaceHolder' : ''}`}
      tabIndex={0}
      onClick={event => {
        event.preventDefault();

        onRedirectToRecord(record);

        return false;
      }}
    >
      <td className="AknGrid-bodyCell">
        <img className="AknGrid-image" src={getImageShowUrl(record.getImage(), 'thumbnail_small')} title="" />
      </td>
      <td className="AknGrid-bodyCell">
        <a
          href={path}
          title={record.getLabel(locale)}
          data-identifier={record.getIdentifier().identifier}
          onClick={event => {
            event.preventDefault();

            onRedirectToRecord(record);

            return false;
          }}
        >
          {record.getLabel(locale)}
        </a>
      </td>
      <td className="AknGrid-bodyCell">
        <a
          href={path}
          title={record.getLabel(locale)}
          data-identifier={record.getIdentifier().identifier}
          onClick={event => {
            event.preventDefault();

            onRedirectToRecord(record);

            return false;
          }}
        >
          {record.getCode().stringValue()}
        </a>
      </td>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--actions action-cell">
        <div className="AknButtonList AknButtonList--right" />
      </td>
    </tr>
  );
};
