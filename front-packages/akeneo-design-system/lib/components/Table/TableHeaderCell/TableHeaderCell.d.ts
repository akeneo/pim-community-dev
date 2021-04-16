import React, { ReactNode } from 'react';
declare type TableSortDirection = 'descending' | 'ascending' | 'none';
declare type TableHeaderCellProps = {
    isSortable?: boolean;
    onDirectionChange?: (direction: TableSortDirection) => void;
    sortDirection?: TableSortDirection;
    children?: ReactNode;
};
declare const TableHeaderCell: React.ForwardRefExoticComponent<TableHeaderCellProps & React.RefAttributes<HTMLTableHeaderCellElement>>;
export { TableHeaderCell };
