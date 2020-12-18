import React, {Children, PropsWithChildren, ReactElement} from 'react';
import {TableBody} from '../layouts/tables';
import {Row} from './Row';

type RowClickHandler<T> = (data: T) => void;
type RowMoveEndHandler<T> = (data: T) => void;

type Props<T> = {
  onRowClick?: RowClickHandler<T>;
  onRowMoveEnd?: RowMoveEndHandler<T>;
};

const Body = <T extends {}>({children, onRowClick, onRowMoveEnd}: PropsWithChildren<Props<T>>) => {
  return (
    <TableBody>
      {Children.map(children, (child, index) => {
        const element = child as ReactElement;
        if (element.type === Row) {
          return React.cloneElement(element, {
            index,
            handleClick: onRowClick,
            handleDrop: onRowMoveEnd,
          });
        }
        return child;
      })}
    </TableBody>
  );
};

export {Body};
