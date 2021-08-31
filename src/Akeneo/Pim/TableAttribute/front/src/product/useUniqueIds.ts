import {TableRow, TableValue} from '../models/TableValue';
import {uuid} from 'akeneo-design-system';
import {TableValueWithId} from './TableFieldApp';

const useUniqueIds: () => {
  addUniqueIds: (value: TableValue) => TableValueWithId;
  removeUniqueIds: (value: TableValueWithId) => TableValue;
} = () => {
  const addUniqueIds = (value: TableValue) => {
    return value.map(row => {
      return Object.keys(row).reduce(
        (previousRow: TableRow & {'unique id': string}, columnCode) => {
          previousRow[columnCode] = row[columnCode];

          return previousRow;
        },
        {'unique id': uuid()}
      );
    });
  };

  const removeUniqueIds = (value: TableValueWithId) => {
    return value.map(row => {
      return Object.keys(row)
        .filter(columnCode => columnCode !== 'unique id')
        .reduce((newRow: TableRow, columnCode) => {
          newRow[columnCode] = row[columnCode];
          return newRow;
        }, {});
    });
  };

  return {addUniqueIds, removeUniqueIds};
};

export {useUniqueIds};
