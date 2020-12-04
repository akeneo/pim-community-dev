import React, {PropsWithChildren, ReactElement, RefObject} from 'react';
import {TableCell} from '../layouts/tables';
import {useDataGridState} from '../../../hooks';
import {AfterDropRowHandler} from '../providers';
import {RowIcon} from 'akeneo-design-system';
import styled from 'styled-components';

type Props<T> = {
  data: T;
  index: number;
  handleDrop: AfterDropRowHandler;
  rowRef: RefObject<HTMLElement>;
};

const DraggableCell = styled(TableCell)`
  line-height: 10px;
  color: ${({theme}) => theme.color.grey100};
`;

const DraggableRowWrapper = <T extends {}>({
  children,
  index,
  data,
  rowRef,
  handleDrop,
}: PropsWithChildren<Props<T>>) => {
  const {isReorderAllowed, moveOver, moveStart, moveDrop, moveEnd, isReorderActive} = useDataGridState();

  return (
    <>
      {isReorderAllowed && (
        <DraggableCell
          isDraggable={isReorderAllowed}
          isActive={isReorderActive}
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
            moveEnd(event, handleDrop);
          }}
        >
          <RowIcon shapeRendering="crispEdges" />
        </DraggableCell>
      )}
      {React.Children.map(children, element => {
        return React.cloneElement(element as ReactElement, {
          draggable: isReorderAllowed && isReorderActive,
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
