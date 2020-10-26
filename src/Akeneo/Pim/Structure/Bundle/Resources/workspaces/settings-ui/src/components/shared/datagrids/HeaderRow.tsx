import React, {FC} from 'react';
import {TableHead, TableHeadCell, TableHeadRow} from '../layouts/tables';
import {useDataGridState} from '../../../hooks/shared';

type Props = {
  isDraggable?: boolean;
};

const HeaderRow: FC<Props> = ({children, isDraggable = false}) => {
  const {isFilterable} = useDataGridState();
  return (
    <TableHead>
      <TableHeadRow>
        {isDraggable && <TableHeadCell isFilterable={isFilterable} style={{width: 35}} />}
        {React.Children.map(children, element => (
          <TableHeadCell isFilterable={isFilterable}>{element}</TableHeadCell>
        ))}
      </TableHeadRow>
    </TableHead>
  );
};

export {HeaderRow};
