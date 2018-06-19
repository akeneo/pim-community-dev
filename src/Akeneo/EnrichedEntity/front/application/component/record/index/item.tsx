import * as React from 'react';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import { getImageShowUrl } from 'akeneoenrichedentity/tools/media-url-generator';
const router = require('pim/router');

export default ({
  record,
  locale,
  isLoading = false,
  onRedirectToRecord
}: {
  record: Record;
  locale: string;
  isLoading?: boolean;
  position: number;
} & {
  onRedirectToRecord: (record: Record) => void;
}) => {
  const path =
    '' !== record.getIdentifier().stringValue()
      ? `#${router.generate('akeneo_enriched_entities_records_edit', {
          enrichedEntityIdentifier: record.getEnrichedEntityIdentifier().stringValue(),
          identifier: record.getIdentifier().stringValue()
        })}`
      : '';

  return (
    <a
      href={path}
      title={record.getLabel(locale)}
      className={`AknGrid-bodyRow AknGrid-bodyRow--thumbnail AknGrid-bodyRow--withoutTopBorder ${
        isLoading ? "AknLoadingPlaceHolder" : ""
      }`}
      data-identifier={record.getIdentifier().stringValue()}
      onClick={event => {
        event.preventDefault();

        onRedirectToRecord(record);

        return false;
      }}
    >
      <span
        className="AknGrid-fullImage"
        style={{
          backgroundImage: `url("${getImageShowUrl(null, "thumbnail")}")`
        }}
      />
      <span className="AknGrid-title">{record.getLabel(locale)}</span>
      <span className="AknGrid-subTitle">
        {record.getIdentifier().stringValue()}
      </span>
      <span className="AknGrid-bodyCell AknGrid-bodyCell--tight AknGrid-bodyCell--checkbox" />
      <span className="AknGrid-bodyCell AknGrid-bodyCell--actions">
        <div className="AknButtonList AknButtonList--right" />
      </span>
    </a>
  );
};
