import {createContext} from 'react';

type TableContextType = {
  isSelectable: boolean;
  hasWarningRows: boolean;
  hasLockedRows: boolean;
  displayCheckbox: boolean;
  isDragAndDroppable: boolean;
  onReorder: ((reorderedIndices: number[]) => void) | undefined;
};

const TableContext = createContext<TableContextType>({
  isSelectable: false,
  hasWarningRows: false,
  hasLockedRows: false,
  displayCheckbox: false,
  isDragAndDroppable: false,
  onReorder: undefined,
});

export {TableContext};
