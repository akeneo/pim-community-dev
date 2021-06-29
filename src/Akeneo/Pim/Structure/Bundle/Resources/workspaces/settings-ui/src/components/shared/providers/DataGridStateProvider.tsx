import React, {createContext, PropsWithChildren} from 'react';
import {useInitialDataGridState} from '../../../hooks';

enum MoveDirection {
  Up,
  Down,
}

type AfterMoveRowHandler<T> = (reorderedDataSource: T[]) => void;
type CompareRowDataHandler<T> = (source: T, target: T) => number;
type MoveRowHandler<T> = (source: T, target: T) => void;
type AfterDropRowHandler = () => void;
type StartMoveRowHandler<T> = (event: React.DragEvent, source: T, index: number, rowElement: Element | null) => void;
type MoveOverRowHandler<T> = (event: React.DragEvent, target: T, index: number) => void;
type MoveDropRowHandler = (event: React.DragEvent, handleDropRow: AfterDropRowHandler) => void;
type MoveEndRowHandler = (event: React.DragEvent, handleDropRow: AfterDropRowHandler) => void;

type DataGridState<T> = {
  draggedData: T | null;
  draggedIndex: number;
  isReorderAllowed: boolean;
  isReorderActive: boolean;
  dataSource: T[];
  isDragged: (data: T) => boolean;
  moveUp: MoveRowHandler<T>;
  moveDown: MoveRowHandler<T>;
  moveStart: StartMoveRowHandler<T>;
  moveOver: MoveOverRowHandler<T>;
  moveDrop: MoveDropRowHandler;
  moveEnd: MoveEndRowHandler;
  isFilterable: boolean;
};

type Props<T> = {
  isReorderAllowed: boolean;
  isReorderActive: boolean;
  dataSource: T[];
  handleAfterMove: AfterMoveRowHandler<T>;
  compareData: CompareRowDataHandler<T>;
  isFilterable: boolean;
};

const DEFAULT_DRAGGED_INDEX = -1;

const DataGridStateContext = createContext<DataGridState<any>>({
  draggedData: null,
  draggedIndex: DEFAULT_DRAGGED_INDEX,
  isReorderAllowed: false,
  isReorderActive: false,
  dataSource: [],
  isDragged: () => false,
  moveUp: () => {},
  moveDown: () => {},
  moveStart: () => {},
  moveOver: () => {},
  moveDrop: () => {},
  moveEnd: () => {},
  isFilterable: false,
});

const DataGridStateProvider = <T extends {}>({
  children,
  isReorderAllowed,
  isReorderActive,
  dataSource,
  handleAfterMove,
  compareData,
  isFilterable,
}: PropsWithChildren<Props<T>>) => {
  const state = useInitialDataGridState(
    isReorderAllowed,
    dataSource,
    handleAfterMove,
    compareData,
    isFilterable,
    isReorderActive
  );

  return <DataGridStateContext.Provider value={state}>{children}</DataGridStateContext.Provider>;
};

export {
  MoveDirection,
  DataGridStateContext,
  DataGridState,
  DataGridStateProvider,
  AfterMoveRowHandler,
  AfterDropRowHandler,
  MoveRowHandler,
  StartMoveRowHandler,
  MoveOverRowHandler,
  MoveDropRowHandler,
  MoveEndRowHandler,
  CompareRowDataHandler,
  DEFAULT_DRAGGED_INDEX,
};
