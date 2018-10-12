import * as React from 'react';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';

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
  onRedirectToRecord: (record: NormalizedRecord) => void;
}) => {
  return (
    <tr
      className={`AknGrid-bodyRow AknGrid-bodyRow--withoutTopBorder ${isLoading ? 'AknLoadingPlaceHolder' : ''}`}
      data-identifier={record.identifier}
    >
      <td className="AknGrid-bodyCell AknGrid-bodyCell--action">
        <div className="AknButtonList AknButtonList--right">
          <a
            className="AknIconButton AknIconButton--small AknIconButton--trash AknButtonList-item"
            data-identifier={record.identifier}
            onClick={event => {
              event.preventDefault();

              onRedirectToRecord(record);

              return false;
            }}
          />
          <a
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
};
