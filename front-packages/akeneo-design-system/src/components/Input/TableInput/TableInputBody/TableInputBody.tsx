import styled from 'styled-components';
import React, {Children, cloneElement, ReactNode, Ref, useContext} from 'react';
import {getColor} from '../../../../theme';
import {TableInputContext} from '../TableInputContext';
import {TableInputRowProps} from '../TableInputRow/TableInputRow';
import {useDragElementIndex} from '../../../../hooks/useDragElementIndex';
import {useDrop} from '../../../../hooks/useDrop';

const TableInputTbody = styled.tbody`
  /*
  & > tr > td {
    background: ${getColor('white')};
  }
  & > tr:nth-child(2n) > td {
    background: ${getColor('grey', 20)};
  }
*/
`;

type TableInputBodyProps = {
  children?: ReactNode;
};

const TableInputBody = React.forwardRef<HTMLTableSectionElement, TableInputBodyProps>(
  ({children, ...rest}: TableInputBodyProps, forwardedRef: Ref<HTMLTableSectionElement>) => {
    const [draggedElementIndex, onDragStart, onDragEnd] = useDragElementIndex();
    const {isDragAndDroppable, onReorder} = useContext(TableInputContext);

    const decoratedChildren = Children.map(children, (child, rowIndex) => {
      if (!React.isValidElement<TableInputRowProps>(child)) {
        return null;
      }

      return isDragAndDroppable
        ? cloneElement(child, {
            rowIndex,
            draggedElementIndex,
            onDragStart: () => onDragStart(rowIndex),
            onDragEnd,
          })
        : cloneElement(child, {
            rowIndex,
          });
    });

    const rowCount = Children.count(decoratedChildren);
    const [tableId, onDrop, onDragOver] = useDrop(rowCount, draggedElementIndex, onReorder);

    return (
      <TableInputTbody data-table-id={tableId} onDrop={onDrop} onDragOver={onDragOver} ref={forwardedRef} {...rest}>
        {decoratedChildren}
      </TableInputTbody>
    );
  }
);

TableInputBody.displayName = 'TableInput.Body';

export {TableInputBody};
