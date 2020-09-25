import React, {FC} from 'react';
import {TableHead, TableHeadCell, TableHeadRow} from "../layouts/tables";

type Props = {};

const HeaderRow:FC<Props> = ({children})=> {
    const isDraggable = true;
    return (
        <TableHead>
            <TableHeadRow>
                {isDraggable && (<TableHeadCell />)}
                {React.Children.map(children, (element) => (
                    <TableHeadCell>{element}</TableHeadCell>
                ))}
            </TableHeadRow>
        </TableHead>
    );
};

export {HeaderRow}