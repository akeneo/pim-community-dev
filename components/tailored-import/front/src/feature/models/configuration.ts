import {uuid} from 'akeneo-design-system';

type Column = {
  uuid: string;
  index: number;
  label: string;
};

type StructureConfiguration = {
  columns: Column[];
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

export type {StructureConfiguration, Column};
export {generateColumns};
