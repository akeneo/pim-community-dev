import React, { ReactNode, SyntheticEvent } from 'react';
declare type TableRowProps = {
    children?: ReactNode;
    onSelectToggle?: (isSelected: boolean) => void;
    isSelected?: boolean;
    onClick?: (event: SyntheticEvent) => void;
};
declare const TableRow: React.ForwardRefExoticComponent<TableRowProps & React.RefAttributes<HTMLTableRowElement>>;
export { TableRow };
