import {ColumnCode, TableAttribute, TableCell} from '../../models';
import {TableValueWithId} from '../TableFieldApp';

export type CellMatcher = (
  attribute: TableAttribute,
  valueData: TableValueWithId
) => (cell: TableCell, searchText: string, columnCode: ColumnCode) => boolean;

export type CellMatchersMapping = {
  [data_type: string]: {
    default: CellMatcher;
  };
};
