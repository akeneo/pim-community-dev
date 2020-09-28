import React, {PropsWithChildren, ReactElement, RefObject} from 'react';
import {TableCell} from '../layouts/tables';
import {MoveIcon} from '@akeneo-pim-community/shared/src';
import {useDataGridState} from '../../../hooks';
import {AfterDropRowHandler} from '../providers';

type Props<T> = {
  data: T;
  index: number;
  handleDrop: AfterDropRowHandler;
  rowRef: RefObject<HTMLElement>;
};

const DraggableRowWrapper = <T extends {}>({
  children,
  index,
  data,
  rowRef,
  handleDrop,
}: PropsWithChildren<Props<T>>) => {
  const {isDraggable, moveOver, moveStart, moveDrop, moveEnd} = useDataGridState();

  return (
    <>
      {isDraggable && (
        <TableCell
          width={40}
          isDraggable={true}
          onDragStart={event => {
            moveStart(event, data, index, rowRef.current);
          }}
          onDragOver={event => {
            moveOver(event, data, index);
          }}
          onDrop={event => {
            moveDrop(event, handleDrop);
          }}
          onDragEnd={event => {
            moveEnd(event);
          }}
        >
          <MoveIcon />
        </TableCell>
      )}
      {React.Children.map(children, element => {
        return React.cloneElement(element as ReactElement, {
          draggable: true,
          onDragStart: (event: React.DragEvent) => {
            event.stopPropagation();
            event.preventDefault();
          },
          onDragOver: (event: React.DragEvent) => {
            moveOver(event, data, index);
          },
          onDrop: (event: React.DragEvent) => {
            moveDrop(event, handleDrop);
          },
        });
      })}
    </>
  );
};

export {DraggableRowWrapper};
