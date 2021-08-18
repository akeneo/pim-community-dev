import styled, {css} from 'styled-components';
import React, {forwardRef, HTMLAttributes, ReactNode, Ref, useContext, useEffect} from 'react';
import {AkeneoThemedProps, getColor} from '../../../../theme';
import {Override} from '../../../../shared';
import {TableInputContext} from '../TableInputContext';
import {RowIcon} from '../../../../icons';
import {TableInputCell} from '../TableInputCell/TableInputCell';
import {useBooleanState} from '../../../../hooks';
import {PlaceholderPosition, usePlaceholderPosition} from '../../../../hooks/usePlaceholderPosition';

const getZebraBackgroundColor: (highlighted: boolean, rowIndex: number) => (props: AkeneoThemedProps) => string = (
  highlighted,
  rowIndex
) => {
  return highlighted ? getColor('blue', 10) : rowIndex % 2 === 0 ? getColor('white') : getColor('grey', 20);
};

const TableInputTr = styled.tr<
  {
    placeholderPosition: PlaceholderPosition;
    rowIndex: number;
    isDragAndDroppable: boolean;
    highlighted: boolean;
  } & AkeneoThemedProps
>`
  height: 40px;
  & > td {
    border: 1px solid ${getColor('grey', 60)};
    border-right-width: 0;
    border-top-width: 0;
    line-height: 39px;
  }
  & > td:first-child {
    position: sticky;
    left: 0;
    margin-right: -1px;
    z-index: 2;
  }

  ${({isDragAndDroppable}) =>
    isDragAndDroppable &&
    css`
      & > td:nth-child(2) {
        position: sticky;
        left: 26px;
        z-index: 1;
        border-left: none;
      }
    `}

  & > td:last-child {
    border-right-width: 1px;
  }

  ${({placeholderPosition, rowIndex, highlighted}) =>
    placeholderPosition === 'bottom' &&
    css`
      & > td {
        background: linear-gradient(
          to top,
          ${getColor('blue', 40)} 4px,
          ${getZebraBackgroundColor(highlighted, rowIndex)} 0px
        );
      }
    `}

  ${({placeholderPosition, rowIndex, highlighted}) =>
    placeholderPosition === 'top' &&
    css`
      & > td {
        background: linear-gradient(
          to bottom,
          ${getColor('blue', 40)} 4px,
          ${getZebraBackgroundColor(highlighted, rowIndex)} 0px
        );
      }
    `}
  
  ${({placeholderPosition, rowIndex, highlighted}) =>
    placeholderPosition === 'none' &&
    css`
      & > td {
        background: ${getZebraBackgroundColor(highlighted, rowIndex)};
      }
    `}
    
  ${({highlighted}) =>
    highlighted &&
    css`
      & > td {
        &:before {
          content: '';
          border-bottom: 1px solid ${getColor('blue', 100)};
          position: relative;
          width: 100%;
          display: block;
          height: 0;
          margin-top: -1px;
        }
        &:has(div) {
          background: red !important;
        }
        /*box-shadow: 0 -1px 0px ${getColor('blue', 100)};*/
        border-bottom-color: ${getColor('blue', 100)};
        &:first-child {
          border-left: 1px solid ${getColor('blue', 100)};
        }
        &:last-child {
          border-right: 1px solid ${getColor('blue', 100)};
        }
      }
    `}
`;

const DragAndDropCell = styled(TableInputCell)`
  max-width: 26px;
  min-width: 26px;
  width: 26px;
  color: ${getColor('grey', 100)};
  text-align: right;
  cursor: grab;
  vertical-align: middle;
  line-height: 0px !important;
`;

export type TableInputRowProps = Override<
  HTMLAttributes<HTMLTableRowElement>,
  {
    /**
     * Content of the row
     */
    children?: ReactNode;

    /**
     * Define if this row is highlighted
     */
    highlighted?: boolean;

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
    {children, rowIndex = 0, draggedElementIndex = null, highlighted = false, ...rest}: TableInputRowProps,
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
        highlighted={highlighted}
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
