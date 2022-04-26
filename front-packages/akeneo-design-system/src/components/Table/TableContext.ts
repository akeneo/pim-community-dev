import {createContext} from 'react';

type TableContextType = {
  isSelectable: boolean;
  hasWarnedRows: boolean;
  displayCheckbox: boolean;
  isDragAndDroppable: boolean;
  onReorder: ((reorderedIndices: number[]) => void) | undefined;
};

const TableContext = createContext<TableContextType>({
  isSelectable: false,
  hasWarnedRows: false,
  displayCheckbox: false,
  isDragAndDroppable: false,
  onReorder: undefined,
});

export {TableContext};
