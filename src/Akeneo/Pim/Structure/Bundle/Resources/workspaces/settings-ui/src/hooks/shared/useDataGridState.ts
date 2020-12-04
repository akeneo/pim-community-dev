import React, {useCallback, useContext, useState} from 'react';
import {
  AfterDropRowHandler,
  AfterMoveRowHandler,
  CompareRowDataHandler,
  DataGridState,
  DataGridStateContext,
  DEFAULT_DRAGGED_INDEX,
  MoveDirection,
} from '../../components/shared';

import {handleDragEnd, handleDragOver, handleDragStart, handleDrop} from '../../events';

const useDataGridState = <T extends {}>(): DataGridState<T> => {
  const context = useContext(DataGridStateContext);

  if (!context) {
    throw new Error("[Context]: You are trying to use 'DataGridState' context outside Provider");
  }

  return context;
};

const useInitialDataGridState = <T extends {}>(
  isReorderAllowed: boolean,
  dataSource: T[],
  handleAfterMove: AfterMoveRowHandler<T>,
  compareRowData: CompareRowDataHandler<T>,
  isFilterable: boolean,
  isReorderActive: boolean
): DataGridState<T> => {
  const [draggedData, setDraggedData] = useState<T | null>(null);
  const [draggedIndex, setDraggedIndex] = useState<number>(DEFAULT_DRAGGED_INDEX);

  const isSameData = (source: T, target: T): boolean => {
    return compareRowData(source, target) === 0;
  };

  const move = useCallback(
    (source: T, target: T, direction: MoveDirection) => {
      const newDataSourceList: T[] = [];
      let order = 0;
      let newIndex = DEFAULT_DRAGGED_INDEX;

      dataSource.forEach((data: T) => {
        if (isSameData(data, source)) {
          return;
        }

        if (direction == MoveDirection.Up && isSameData(data, target)) {
          newDataSourceList.push(source);
          newIndex = order;
          order++;
        }

        newDataSourceList.push(data);
        order++;

        if (direction == MoveDirection.Down && isSameData(data, target)) {
          newDataSourceList.push(source);
          newIndex = order;
          order++;
        }
      });

      setDraggedIndex(newIndex);
      handleAfterMove(newDataSourceList);
    },
    [dataSource, handleAfterMove, setDraggedIndex, compareRowData]
  );

  const moveUp = useCallback(
    (source: T, target: T) => {
      move(source, target, MoveDirection.Up);
    },
    [move]
  );

  const moveDown = useCallback(
    (source: T, target: T) => {
      move(source, target, MoveDirection.Down);
    },
    [move]
  );

  const isDragged = useCallback(
    (data: T) => {
      if (draggedData === null) {
        return false;
      }

      return isSameData(draggedData, data);
    },
    [draggedData]
  );

  const moveStart = useCallback(
    (event: React.DragEvent, source: T, index: number, rowElement: Element | null) => {
      handleDragStart(event, rowElement);

      setDraggedData(source);
      setDraggedIndex(index);
    },
    [setDraggedData, setDraggedIndex]
  );

  const moveOver = useCallback(
    (event: React.DragEvent, target: T, index: number) => {
      handleDragOver(event, draggedIndex, draggedData, index, target, moveDown, moveUp, isSameData);
    },
    [draggedData, draggedIndex, moveDown, moveUp]
  );

  const moveDrop = useCallback(
    (event: React.DragEvent, handleDropRow: AfterDropRowHandler) => {
      handleDrop(event, () => {
        if (draggedData === null) {
          return;
        }

        handleDropRow();

        setDraggedData(null);
        setDraggedIndex(DEFAULT_DRAGGED_INDEX);
      });
    },
    [draggedData, setDraggedData, setDraggedIndex]
  );

  const moveEnd = useCallback(
    (event: React.DragEvent, handleDropRow: AfterDropRowHandler) => {
      handleDragEnd(event, () => {
        if (draggedData === null) {
          return;
        }

        handleDropRow();
        setDraggedData(null);
        setDraggedIndex(DEFAULT_DRAGGED_INDEX);
      });
    },
    [setDraggedData, setDraggedIndex, draggedData]
  );

  return {
    draggedData,
    draggedIndex,
    isReorderAllowed,
    isReorderActive,
    dataSource,
    isDragged,
    moveUp,
    moveDown,
    moveStart,
    moveOver,
    moveDrop,
    moveEnd,
    isFilterable,
  };
};

export {useDataGridState, useInitialDataGridState};
