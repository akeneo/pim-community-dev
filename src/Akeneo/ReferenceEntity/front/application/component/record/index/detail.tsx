import * as React from 'react';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';
import {CellViews} from 'akeneoreferenceentity/application/component/reference-entity/edit/record';
import {RowView} from 'akeneoreferenceentity/application/component/record/index/table';

const DetailView: RowView = React.memo(
  ({
    record,
    isLoading = false,
    onRedirectToRecord,
    columns,
    cellViews,
  }: {
    record: NormalizedRecord;
    isLoading?: boolean;
    position: number;
    columns: Column[];
    cellViews: CellViews;
  } & {
    onRedirectToRecord: (record: NormalizedRecord) => void;
  }) => {
    if (true === isLoading) {
      return (
        <tr className="AknGrid-bodyRow AknGrid-bodyRow--withoutTopBorder">
          {columns.map((colum: Column) => {
            return (
              <td key={colum.key} className="AknGrid-bodyCell">
                <div className="AknGrid-bodyCellContainer AknLoadingPlaceHolder" />
              </td>
            );
          })}
        </tr>
      );
    }

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
        {columns.map((column: Column) => {
          const CellView = cellViews[column.key];
          const value = record.values[column.key as any];

          if (undefined === value) {
            return <td key={column.key} className="AknGrid-bodyCell" />;
          }

          return (
            <td key={column.key} className="AknGrid-bodyCell">
              <CellView value={value} />
            </td>
          );
        })}
      </tr>
    );
  }
);

export default DetailView;
