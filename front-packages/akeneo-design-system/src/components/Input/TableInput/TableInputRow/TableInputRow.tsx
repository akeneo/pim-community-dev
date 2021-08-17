import styled, {css} from 'styled-components';
import React, {forwardRef, HTMLAttributes, ReactNode, Ref, useContext, useEffect} from 'react';
import {AkeneoThemedProps, getColor} from '../../../../theme';
import {Override} from '../../../../shared';
import {TableInputContext} from '../TableInputContext';
import {RowIcon} from '../../../../icons';
import {TableInputCell} from '../TableInputCell/TableInputCell';
import {useBooleanState} from '../../../../hooks';
import {PlaceholderPosition, usePlaceholderPosition} from '../../../../hooks/usePlaceholderPosition';

const getZebraBackgroundColor: (rowIndex: number) => (props: AkeneoThemedProps) => string = rowIndex => {
  return rowIndex % 2 === 0 ? getColor('white') : getColor('grey', 20);
};

const TableInputTr = styled.tr<
  {placeholderPosition: PlaceholderPosition; rowIndex: number; isDragAndDroppable: boolean} & AkeneoThemedProps
>`
  height: 40px;
  & > td {
    border: 1px solid ${getColor('grey', 60)};
    border-right-width: 0;
    border-top-width: 0;
  }
  & > td:first-child {
    position: sticky;
    left: 0;
    z-index: 2;
  }
  ${({isDragAndDroppable}) =>
    isDragAndDroppable &&
    css`
      & > td:nth-child(2) {
        position: sticky;
        left: 27px;
        z-index: 1;
      }
    `}

  & > td:last-child {
    border-right-width: 1px;
  }

  & > td:nth-child(2) {
    border-left: none;
  }

  ${({placeholderPosition, rowIndex}) =>
    placeholderPosition === 'bottom' &&
    css`
      & > td {
        background: linear-gradient(to top, ${getColor('blue', 40)} 4px, ${getZebraBackgroundColor(rowIndex)} 0px);
      }
    `}

  ${({placeholderPosition, rowIndex}) =>
    placeholderPosition === 'top' &&
    css`
      & > td {
        background: linear-gradient(to bottom, ${getColor('blue', 40)} 4px, ${getZebraBackgroundColor(rowIndex)} 0px);
      }
    `}
  
  ${({placeholderPosition, rowIndex}) =>
    placeholderPosition === 'none' &&
    css`
      & > td {
        background: ${getZebraBackgroundColor(rowIndex)};
      }
    `}
`;

const DragAndDropCell = styled(TableInputCell)`
  max-width: 26px;
  min-width: 26px;
  width: 26px;
  color: ${getColor('grey', 100)};
  padding-top: 5px;
  text-align: right;
  cursor: grab;
`;

export type TableInputRowProps = Override<
  HTMLAttributes<HTMLTableRowElement>,
  {
    /**
     * Content of the row
     */
    children?: ReactNode;

    /**
     * @private
     */
    rowIndex?: number;

    /**
     * @private
     */
    draggedElementIndex?: number | null;
  }
>;

const TableInputRow = forwardRef<HTMLTableRowElement, TableInputRowProps>(
  (
    {children, rowIndex = 0, draggedElementIndex = null, ...rest}: TableInputRowProps,
    forwardedRef: Ref<HTMLTableRowElement>
  ) => {
    const [isDragged, drag, drop] = useBooleanState();
    const [placeholderPosition, dragEnter, dragLeave, dragEnd] = usePlaceholderPosition(rowIndex, draggedElementIndex);
    const {isDragAndDroppable} = useContext(TableInputContext);

    useEffect(() => {
      if (null === draggedElementIndex) {
        drop();
        dragEnd();
      }
    }, [draggedElementIndex]);

    return (
      <TableInputTr
        draggable={isDragAndDroppable && isDragged}
        isDragAndDroppable={isDragAndDroppable}
        data-draggable-index={rowIndex}
        onDragEnter={dragEnter}
        onDragLeave={dragLeave}
        ref={forwardedRef}
        placeholderPosition={placeholderPosition}
        rowIndex={rowIndex}
        {...rest}
      >
        {isDragAndDroppable && (
          <DragAndDropCell onMouseDown={drag} onMouseUp={drop} data-testid="dragAndDrop">
            <RowIcon size={16} />
          </DragAndDropCell>
        )}
        {children}
      </TableInputTr>
    );
  }
);

TableInputRow.displayName = 'TableInput.Row';

export {TableInputRow};
