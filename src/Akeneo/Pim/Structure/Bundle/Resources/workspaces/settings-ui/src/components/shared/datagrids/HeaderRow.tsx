import React, {FC} from 'react';
import {TableHead, TableHeadCell, TableRow} from "../layouts/tables";

type Props = {};

const HeaderRow:FC<Props> = ({children})=> {
    const isDraggable = true;
    return (
        <TableHead>
            <TableRow>
                {isDraggable && (<TableHeadCell />)}
                {React.Children.map(children, (element) => (
                    <TableHeadCell>{element}</TableHeadCell>
                ))}
            </TableRow>
        </TableHead>
    );
};

export {HeaderRow}