import * as React from 'react';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
import {denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
const router = require('pim/router');

export default ({
  record,
  isLoading = false,
  onRedirectToRecord,
}: {
  record: NormalizedRecord;
  isLoading?: boolean;
  position: number;
} & {
  onRedirectToRecord: (record: NormalizedRecord) => void;
}) => {
  const path =
    '' !== record.identifier
      ? `#${router.generate('akeneo_reference_entities_record_edit', {
          referenceEntityIdentifier: record.reference_entity_identifier,
          recordCode: record.code,
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
      <td className="AknGrid-bodyCell AknGrid-bodyCell--image">
        <img className="AknGrid-image" src={getImageShowUrl(denormalizeFile(record.image), 'thumbnail_small')} title="" />
      </td>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--identifier">
        <a
          href={path}
          title={record.code}
          data-identifier={record.identifier}
          onClick={event => {
            event.preventDefault();

            onRedirectToRecord(record);

            return false;
          }}
        >
          {record.code}
        </a>
      </td>
    </tr>
  );
};
