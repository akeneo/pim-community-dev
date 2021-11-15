import styled, {css} from 'styled-components';
import React, {forwardRef, HTMLAttributes, ReactNode, Ref, useContext, DragEvent} from 'react';
import {AkeneoThemedProps, getColor} from '../../../../theme';
import {Override} from '../../../../shared';
import {TableInputContext} from '../TableInputContext';
import {RowIcon} from '../../../../icons';
import {TableInputCell} from '../TableInputCell/TableInputCell';
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
  & > div {
    height: 39px;
    vertical-align: middle;
    line-height: 44px;
  }
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
    onDragStart?: (rowIndex: number) => void;

    /**
     * @private
     */
    onDragEnd?: () => void;
  }
>;

const TableInputRow = forwardRef<HTMLTableRowElement, TableInputRowProps>(
  (
    {children, rowIndex = 0, draggable, highlighted = false, onDragStart, onDragEnd, ...rest}: TableInputRowProps,
    forwardedRef: Ref<HTMLTableRowElement>
  ) => {
    const [
      placeholderPosition,
      placeholderDragEnter,
      placeholderDragLeave,
      placeholderDragEnd,
    ] = usePlaceholderPosition(rowIndex);

    const {isDragAndDroppable} = useContext(TableInputContext);

    const handleDragEnter = (event: DragEvent<HTMLTableRowElement>) => {
      if (isDragAndDroppable) {
        placeholderDragEnter(parseInt(event.dataTransfer.getData('text')));
      }
    };

    const handleDragStart = (event: DragEvent<HTMLTableRowElement>) => {
      if (isDragAndDroppable) {
        event.dataTransfer.setData('text', rowIndex.toString());
        onDragStart?.(rowIndex);
      }
    };

    const handleDragEnd = () => {
      if (isDragAndDroppable) {
        placeholderDragEnd();
        onDragEnd?.();
      }
    };

    return (
      <TableInputTr
        highlighted={highlighted}
        draggable={isDragAndDroppable && draggable}
        isDragAndDroppable={isDragAndDroppable}
        data-draggable-index={rowIndex}
        onDragEnter={handleDragEnter}
        onDragLeave={placeholderDragLeave}
        onDragStart={handleDragStart}
        onDragEnd={handleDragEnd}
        ref={forwardedRef}
        placeholderPosition={placeholderPosition}
        rowIndex={rowIndex}
        {...rest}
      >
        {isDragAndDroppable && (
          <DragAndDropCell onMouseDown={() => onDragStart?.(rowIndex)} onMouseUp={onDragEnd} data-testid="dragAndDrop">
            <div>
              <RowIcon size={16} />
            </div>
          </DragAndDropCell>
        )}
        {children}
      </TableInputTr>
    );
  }
);

TableInputRow.displayName = 'TableInput.Row';

export {TableInputRow};
