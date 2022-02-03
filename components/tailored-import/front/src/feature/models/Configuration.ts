import {uuid} from 'akeneo-design-system';
import {DataMapping} from './DataMapping';

type ColumnIdentifier = string;

type Column = {
  uuid: ColumnIdentifier;
  index: number;
  label: string;
};

type StructureConfiguration = {
  columns: Column[];
  data_mappings: DataMapping[];
};

const MAX_COLUMN_COUNT = 500;
const extractColumnLabels = (sheetContent: string): string[] => {
  const rows = sheetContent.split('\n');

  const firstRow = rows[0];
  if ('' === firstRow.trim()) {
    return [];
  }

  return firstRow.split('\t');
};

const generateColumns = (sheetContent: string): Column[] => {
  const columnLabels = extractColumnLabels(sheetContent);
  return columnLabels.slice(0, MAX_COLUMN_COUNT).map((label, index) => ({
    uuid: uuid(),
    index,
    label,
  }));
};

const generateExcelColumnLetter = (index: number): string => {
  if (index <= 25) {
    return `${String.fromCharCode(index + 65)}`;
  }

  const modulo = index % 26;
  const nextIndex = (index - modulo) / 26;

  return `${generateExcelColumnLetter(nextIndex - 1)}${String.fromCharCode(modulo + 65)}`;
};

const generateColumnName = ({index, label}: Column): string => {
  const columnLetter = generateExcelColumnLetter(index);

  return `${label} (${columnLetter})`;
};

export type {StructureConfiguration, Column, ColumnIdentifier};
export {extractColumnLabels, generateColumns, generateColumnName, MAX_COLUMN_COUNT};
