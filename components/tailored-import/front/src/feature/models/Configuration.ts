import {DataMapping} from './DataMapping';

const MAX_COLUMN_COUNT = 500;
const MINIMUM_HEADER_ROW = 1;
const MAXIMUM_HEADER_ROW = 19;
const MINIMUM_FIRST_PRODUCT_ROW = 2;
const MAXIMUM_FIRST_PRODUCT_ROW = 20;

type ColumnIdentifier = string;

type Column = {
  uuid: ColumnIdentifier;
  index: number;
  label: string;
};

type FileStructure = {
  header_row: number;
  first_column: number;
  first_product_row: number;
  unique_identifier_column: number;
  sheet_name: string | null;
};

type ErrorAction = 'skip_value' | 'skip_product';

const isValidErrorAction = (errorAction: string): errorAction is ErrorAction =>
  ['skip_value', 'skip_product'].includes(errorAction);

type StructureConfiguration = {
  import_structure: {
    columns: Column[];
    data_mappings: DataMapping[];
  };
  file_key: string | null;
  error_action: ErrorAction;
  file_structure: FileStructure;
};

const getDefaultFileStructure = (): FileStructure => ({
  header_row: 1,
  first_column: 0,
  first_product_row: 2,
  sheet_name: null,
  unique_identifier_column: 0,
});

const generateExcelColumnLetter = (index: number): string => {
  if (index <= 25) {
    return `${String.fromCharCode(index + 65)}`;
  }

  const modulo = index % 26;
  const nextIndex = (index - modulo) / 26;

  return `${generateExcelColumnLetter(nextIndex - 1)}${String.fromCharCode(modulo + 65)}`;
};

const generateColumnName = (index: number, label: string): string => {
  const columnLetter = generateExcelColumnLetter(index);

  return `${label} (${columnLetter})`;
};

const findColumnByUuid = (columns: Array<Column>, uuid: string): Column | null => {
  return columns.find((column: Column) => column.uuid === uuid) ?? null;
};

export type {StructureConfiguration, Column, ColumnIdentifier, FileStructure, ErrorAction};
export {
  generateColumnName,
  MAX_COLUMN_COUNT,
  MAXIMUM_FIRST_PRODUCT_ROW,
  MINIMUM_FIRST_PRODUCT_ROW,
  MAXIMUM_HEADER_ROW,
  MINIMUM_HEADER_ROW,
  getDefaultFileStructure,
  isValidErrorAction,
  findColumnByUuid,
};
