import {DataMapping} from './DataMapping';

const MAX_COLUMN_COUNT = 500;

type ColumnIdentifier = string;

type Column = {
  uuid: ColumnIdentifier;
  index: number;
  label: string;
};

type FileStructure = {
  header_line: number;
  first_column: number;
  product_line: number;
  column_identifier_position: number;
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
  header_line: 1,
  first_column: 0,
  product_line: 2,
  sheet_name: null,
  column_identifier_position: 0,
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

export type {StructureConfiguration, Column, ColumnIdentifier, FileStructure, ErrorAction};
export {generateColumnName, MAX_COLUMN_COUNT, getDefaultFileStructure, isValidErrorAction};
