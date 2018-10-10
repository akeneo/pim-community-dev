import * as React from 'react';
import Record, {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import denormalizeRecord from 'akeneoreferenceentity/application/denormalizer/record';

export default ({
  record,
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
      <td className="AknGrid-bodyCell AknGrid-bodyCell--action">
        <div className="AknButtonList AknButtonList--right">
          <a
            className="AknIconButton AknIconButton--small AknIconButton--trash AknButtonList-item"
            data-identifier={record.identifier}
            onClick={event => {
              event.preventDefault();

              onRedirectToRecord(denormalizeRecord(record));

              return false;
            }}
          ></a>
        </div>
      </td>
    </tr>
  );
};
