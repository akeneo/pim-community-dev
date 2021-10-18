import {ColumnCode, SelectOptionCode} from '../models';
import {TableRowWithId, TableValueWithId} from './TableFieldApp';
import {uuid} from 'akeneo-design-system';
import React from 'react';
import {UNIQUE_ID_KEY} from './useUniqueIds';

const useToggleRow: (
  tableValue: TableValueWithId,
  firstColumnCode: ColumnCode,
  onChange: (value: TableValueWithId) => void
) => (optionCode: SelectOptionCode) => void = (tableValue: TableValueWithId, firstColumnCode: ColumnCode, onChange) => {
  const [removedRows, setRemovedRows] = React.useState<{[key: string]: TableRowWithId}>({});

  return (optionCode: SelectOptionCode) => {
    const index = tableValue.findIndex(
      row => (row[firstColumnCode] as string).toLowerCase() === optionCode.toLowerCase()
    );
    if (index >= 0) {
      const removed = tableValue.splice(index, 1);
      if (removed.length === 1) {
        removedRows[optionCode.toLowerCase()] = removed[0];
        setRemovedRows({...removedRows});
      }
    } else {
      if (typeof removedRows[optionCode.toLowerCase()] !== 'undefined') {
        tableValue.push(removedRows[optionCode.toLowerCase()]);
      } else {
        const newRow: TableRowWithId = {[UNIQUE_ID_KEY]: uuid()};
        newRow[firstColumnCode] = optionCode;
        tableValue.push(newRow);
      }
    }
    onChange([...tableValue]);
  };
};

export {useToggleRow};
