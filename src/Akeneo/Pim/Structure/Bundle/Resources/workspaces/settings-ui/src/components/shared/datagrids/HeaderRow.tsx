import React, {FC} from 'react';
import {TableHead, TableHeadCell, TableHeadRow} from '../layouts/tables';
import {useDataGridState} from "../../../hooks/shared";

type Props = {};

const HeaderRow: FC<Props> = ({children}) => {
  const {isFilterable} = useDataGridState();
  const isDraggable = true;
  return (
    <TableHead>
      <TableHeadRow>
        {isDraggable && <TableHeadCell isFilterable={isFilterable} style={{width: 35}}/>}
        {React.Children.map(children, element => (
          <TableHeadCell isFilterable={isFilterable}>{element}</TableHeadCell>
        ))}
      </TableHeadRow>
    </TableHead>
  );
};

export {HeaderRow};
