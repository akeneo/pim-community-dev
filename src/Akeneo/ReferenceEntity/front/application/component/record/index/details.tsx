import * as React from 'react';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';

export default ({
  record,
  isLoading = false,
  onRedirectToRecord,
  columns,
}: {
  record: NormalizedRecord;
  isLoading?: boolean;
  position: number;
  columns: Column[];
} & {
  onRedirectToRecord: (record: NormalizedRecord) => void;
}) => {
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
      {columns.map((column: Column) => {
        const value = record.values[column.key as any];
        const text = undefined === value ? '' : value.data;
        const textNode = document.createElement('span');
        textNode.innerHTML = text;
        const safeText = textNode.innerText;

        return (
          <td key={column.key} className="AknGrid-bodyCell">
            <div className="AknGrid-bodyCellContainer" title="safeText">
              {safeText}
            </div>
          </td>
        );
      })}
    </tr>
  );
};
