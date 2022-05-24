type ColumnIdentifier = string;

type Column = {
  uuid: ColumnIdentifier;
  index: number;
  label: string;
};

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

const filterColumnsByUuids = (columns: Column[], uuids: string[]): Column[] =>
  columns.filter(({uuid}) => uuids.includes(uuid));

export type {Column, ColumnIdentifier};
export {generateColumnName, generateExcelColumnLetter, filterColumnsByUuids};
