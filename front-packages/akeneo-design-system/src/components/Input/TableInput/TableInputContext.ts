import {createContext} from 'react';

type TableInputContextType = {
  readOnly: boolean;
  isDragAndDroppable: boolean;
  onReorder: ((reorderedIndices: number[]) => void) | undefined;
};

const TableInputContext = createContext<TableInputContextType>({
  readOnly: false,
  isDragAndDroppable: false,
  onReorder: undefined,
});

export {TableInputContext};
