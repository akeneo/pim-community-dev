import React, {useCallback, memo} from 'react';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';
import {CellViews} from 'akeneoreferenceentity/application/component/reference-entity/edit/record';

type DetailRowProps = {
  record: NormalizedRecord;
  placeholder?: boolean;
  columns: Column[];
  cellViews: CellViews;
  onRedirectToRecord?: (record: NormalizedRecord) => void;
  onSelectionChange?: (recordCode: string, newValue: boolean) => void;
  isItemSelected: (recordCode: string) => boolean;
};

const DetailRow = memo(
  ({
    record,
    placeholder = false,
    onRedirectToRecord,
    onSelectionChange,
    isItemSelected,
    columns,
    cellViews,
  }: DetailRowProps) => {
    const handleSelect = useCallback(
      (newValue: boolean) => {
        onSelectionChange?.(record.code, newValue);
      },
      [onSelectionChange, record]
    );

    if (true === placeholder) {
      return (
        <tr className="AknGrid-bodyRow">
          {columns.map(column => (
            <td key={column.key} className="AknGrid-bodyCell">
              <div className="AknGrid-bodyCellContainer AknLoadingPlaceHolder" />
            </td>
          ))}
        </tr>
      );
    }

    return (
      <tr
        className="AknGrid-bodyRow"
        data-identifier={record.identifier}
        onClick={event => {
          if (undefined !== onRedirectToRecord) {
            event.preventDefault();
            onRedirectToRecord(record);
          } else {
            handleSelect(!isItemSelected(record.code));
          }

          return false;
        }}
      >
        {0 === columns.length ? <td className="AknGrid-bodyCell" /> : null}

        {columns.map(column => {
          const CellView = cellViews[column.key];
          const value = record.values[column.key as any];

          if (undefined === value || undefined === CellView) {
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

type DetailRowsProps = {
  records: NormalizedRecord[];
  locale: string;
  placeholder: boolean;
  onRedirectToRecord?: (record: NormalizedRecord) => void;
  onSelectionChange?: (recordCode: string, newValue: boolean) => void;
  isItemSelected: (recordCode: string) => boolean;
  recordCount: number;
  columns: Column[];
  cellViews: CellViews;
};

const DetailRows = memo(
  ({
    records,
    placeholder,
    onRedirectToRecord,
    onSelectionChange,
    isItemSelected,
    recordCount,
    columns,
    cellViews,
  }: DetailRowsProps) => {
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

      return (
        <>
          {[...Array(placeholderCount)].map((_, key) => (
            <DetailRow
              placeholder={placeholder}
              key={key}
              record={record}
              onSelectionChange={onSelectionChange}
              isItemSelected={isItemSelected}
              columns={columns}
              cellViews={cellViews}
            />
          ))}
        </>
      );
    }

    return (
      <>
        {records.map(record => (
          <DetailRow
            placeholder={false}
            key={record.identifier}
            record={record}
            onRedirectToRecord={onRedirectToRecord}
            onSelectionChange={onSelectionChange}
            isItemSelected={isItemSelected}
            columns={columns}
            cellViews={cellViews}
          />
        ))}
      </>
    );
  }
);

export {DetailRows};
