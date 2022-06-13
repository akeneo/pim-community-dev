type RowInformation = string[];

type FileTemplateInformation = {
  sheet_names: string[];
  rows: RowInformation[];
  column_count: number;
};

const getRowAtPosition = (
  fileTemplateInformation: FileTemplateInformation,
  rowPosition: number,
  columnStart: number = 0
): RowInformation => {
  const emptyRow: RowInformation = Array(fileTemplateInformation.column_count).fill('');
  if (columnStart < 0) {
    return emptyRow;
  }

  const headerCells = fileTemplateInformation.rows[rowPosition - 1] ?? emptyRow;

  return headerCells.slice(columnStart > 0 ? columnStart : 0);
};

const getRowsFromPosition = (
  fileTemplateInformation: FileTemplateInformation,
  rowStart: number,
  columnStart: number = 0
): RowInformation[] => {
  if (rowStart < 1 || columnStart < 0) {
    return [];
  }

  const productRows = fileTemplateInformation.rows.slice(rowStart - 1);

  return productRows.map(row => row.slice(columnStart));
};

export {getRowAtPosition, getRowsFromPosition};
export type {FileTemplateInformation};
