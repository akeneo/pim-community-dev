import {ColumnCode, TableCell} from '../../models';

export type CellMatcher = () => (cell: TableCell, searchText: string, columnCode: ColumnCode) => boolean;

export type CellMatchersMapping = {
  [data_type: string]: {
    default: CellMatcher;
  };
};
