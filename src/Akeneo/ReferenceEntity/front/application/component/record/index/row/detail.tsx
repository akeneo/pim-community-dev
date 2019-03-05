import * as React from 'react';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';
import {CellViews} from 'akeneoreferenceentity/application/component/reference-entity/edit/record';

const memo = (React as any).memo;

const DetailRow = memo(
  ({
    record,
    placeholder = false,
    onRedirectToRecord,
    columns,
    cellViews,
  }: {
    record: NormalizedRecord;
    placeholder?: boolean;
    position: number;
    columns: Column[];
    cellViews: CellViews;
  } & {
    onRedirectToRecord: (record: NormalizedRecord) => void;
  }) => {
    if (true === placeholder) {
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
        {0 === columns.length ? <td className="AknGrid-bodyCell" /> : null}

        {columns.map((column: Column) => {
          const CellView = cellViews[column.key];
          const value = record.values[column.key as any];

          if (undefined === value) {
            return <td key={column.key} className="AknGrid-bodyCell" />;
          }

          return (
            <td key={column.key} className="AknGrid-bodyCell">
              <CellView column={column} value={value} />
            </td>
          );
        })}
      </tr>
    );
  }
);

const DetailRows = memo(
  ({
    records,
    locale,
    placeholder,
    onRedirectToRecord,
    recordCount,
    columns,
    cellViews,
  }: {
    records: NormalizedRecord[];
    locale: string;
    placeholder: boolean;
    onRedirectToRecord: (record: NormalizedRecord) => void;
    recordCount: number;
    columns: Column[];
    cellViews: CellViews;
  }) => {
    if (placeholder) {
      const record = {
        identifier: '',
        reference_entity_identifier: '',
        code: '',
        labels: {},
        image: null,
        values: [],
      };

      const placeholderCount = recordCount < 30 ? recordCount : 30;

      return Array.from(Array(placeholderCount).keys()).map(key => (
        <DetailRow
          placeholder={placeholder}
          key={key}
          record={record}
          locale={locale}
          onRedirectToRecord={() => {}}
          columns={columns}
          cellViews={cellViews}
        />
      ));
    }

    return records.map((record: NormalizedRecord) => {
      return (
        <DetailRow
          placeholder={false}
          key={record.identifier}
          record={record}
          locale={locale}
          onRedirectToRecord={onRedirectToRecord}
          columns={columns}
          cellViews={cellViews}
        />
      );
    });
  }
);

export default DetailRows;
