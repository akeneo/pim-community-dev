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
  dataMappings: DataMapping[];
};

const generateColumns = (sheetContent: string): Column[] => {
  const rows = sheetContent.split('\n');

  const firstRow = rows[0];
  if ('' === firstRow.trim()) {
    return [];
  }

  const columnLabels = firstRow.split('\t');

  return columnLabels.map((label, index) => ({
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
export {generateColumns, generateColumnName};
