import React, { ReactNode } from 'react';
declare type TableCellProps = {
    children?: ReactNode;
    rowTitle?: boolean;
};
declare const TableCell: React.ForwardRefExoticComponent<TableCellProps & React.RefAttributes<HTMLTableCellElement>>;
export { TableCell };
