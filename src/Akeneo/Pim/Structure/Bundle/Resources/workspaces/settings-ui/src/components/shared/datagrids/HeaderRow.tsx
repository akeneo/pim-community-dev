import React, {FC} from 'react';
import {TableHead, TableHeadCell, TableHeadRow} from '../layouts/tables';

type Props = {
  isDraggable?: boolean
};

const HeaderRow: FC<Props> = ({children, isDraggable = false}) => {
  return (
    <TableHead>
      <TableHeadRow>
        {isDraggable && <TableHeadCell />}
        {React.Children.map(children, element => (
          <TableHeadCell>{element}</TableHeadCell>
        ))}
      </TableHeadRow>
    </TableHead>
  );
};

export {HeaderRow};
