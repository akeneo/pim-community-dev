type RowInformation = string[];

type FileTemplateInformation = {
  sheet_names: string[];
  rows: RowInformation[];
  cell_number: number;
};

const getRowAtPosition = (
  fileTemplateInformation: FileTemplateInformation,
  rowPosition: number,
  columnStart: number = 0
) => {
  const headerCells =
    fileTemplateInformation.rows[rowPosition - 1] ?? Array(fileTemplateInformation.cell_number).fill('');

  return headerCells.slice(columnStart);
};

const getRowsAtPosition = (
  fileTemplateInformation: FileTemplateInformation,
  rowStart: number,
  columnStart: number = 0
) => {
  const productRows = fileTemplateInformation.rows.slice(rowStart - 1);

  return productRows.map(row => row.slice(columnStart));
};

export {getRowAtPosition, getRowsAtPosition};
export type {FileTemplateInformation};
