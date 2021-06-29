import React, {isValidElement, PropsWithChildren, useRef} from 'react';
import {TableCell, TableRow} from '../layouts/tables';
import {useDataGridState} from '../../../hooks';
import {AfterDropRowHandler} from '../providers';
import {DraggableRowWrapper} from './DraggableRowWrapper';
import {Cell} from './Cell';

type RowClickHandler<T> = (data: T) => void;

type Props<T> = {
  data: T;
  index?: number;
  handleClick?: RowClickHandler<T>;
  handleDrop?: AfterDropRowHandler;
};

const Row = <T extends {}>({children, index = 0, data, handleDrop, handleClick}: PropsWithChildren<Props<T>>) => {
  const {isDragged} = useDataGridState();
  const rowRef = useRef(null);

  return (
    <TableRow
      ref={rowRef}
      isDragged={isDragged(data)}
      onClick={() => {
        if (handleClick !== undefined) {
          handleClick(data);
        }
      }}
    >
      <DraggableRowWrapper index={index} data={data} rowRef={rowRef} handleDrop={handleDrop ? handleDrop : () => {}}>
        {React.Children.map(children, element => (
          <TableCell rowTitle={isValidElement(element) && element.type === Cell && element.props.rowTitle === true}>
            {element}
          </TableCell>
        ))}
      </DraggableRowWrapper>
    </TableRow>
  );
};

export {Row};
