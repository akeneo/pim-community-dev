import * as React from 'react';
import Record, {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import denormalizeRecord from 'akeneoreferenceentity/application/denormalizer/record';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
const router = require('pim/router');

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
  onRedirectToRecord: (record: Record) => void;
}) => {
  const path =
    '' !== record.identifier
      ? `#${router.generate('akeneo_reference_entities_record_edit', {
          referenceEntityIdentifier: record.reference_entity_identifier,
          recordCode: record.code,
          tab: 'enrich',
        })}`
      : '';

  const label = createLabelCollection(record.labels).getLabel(locale);

  return (
    <tr
      className={`AknGrid-bodyRow AknGrid-bodyRow--withoutTopBorder ${isLoading ? 'AknLoadingPlaceHolder' : ''}`}
      tabIndex={0}
      onClick={event => {
        event.preventDefault();

        onRedirectToRecord(denormalizeRecord(record));

        return false;
      }}
    >
      <td className="AknGrid-bodyCell">
        <a
          href={path}
          title={label}
          data-identifier={record.identifier}
          onClick={event => {
            event.preventDefault();

            onRedirectToRecord(denormalizeRecord(record));

            return false;
          }}
        >
          {label}
        </a>
      </td>
    </tr>
  );
};
