import {createContext} from 'react';

type TableContextType = {
  isSelectable: boolean;
  displayCheckbox: boolean;
  isOrderable: boolean;
  onReorder: ((reorderedIndices: number[]) => void) | undefined;
};

const TableContext = createContext<TableContextType>({
  isSelectable: false,
  displayCheckbox: false,
  isOrderable: false,
  onReorder: undefined,
});

export {TableContext};
