import {TableRow, TableValue} from '../models';
import {uuid} from 'akeneo-design-system';
import {TableRowWithId, TableValueWithId} from './TableFieldApp';

// As we can't have space, the 'unique id' can not be used as column
export const UNIQUE_ID_KEY = 'unique id';

const useUniqueIds: () => {
  addUniqueIds: (value: TableValue) => TableValueWithId;
  removeUniqueIds: (value: TableValueWithId) => TableValue;
} = () => {
  const addUniqueIds = (value: TableValue) => {
    return value.map(row => {
      return Object.keys(row).reduce(
        (previousRow: TableRowWithId, columnCode) => {
          previousRow[columnCode] = row[columnCode];

          return previousRow;
        },
        {[UNIQUE_ID_KEY]: uuid()}
      );
    });
  };

  const removeUniqueIds = (value: TableValueWithId) => {
    return value.map(row => {
      return Object.keys(row)
        .filter(columnCode => columnCode !== UNIQUE_ID_KEY)
        .reduce((newRow: TableRow, columnCode) => {
          newRow[columnCode] = row[columnCode];
          return newRow;
        }, {});
    });
  };

  return {addUniqueIds, removeUniqueIds};
};

export {useUniqueIds};
