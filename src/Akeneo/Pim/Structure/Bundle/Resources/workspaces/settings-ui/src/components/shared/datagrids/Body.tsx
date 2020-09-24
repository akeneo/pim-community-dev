import React, {FC} from 'react';
import {TableBody} from "../layouts/tables";

type Props = {};

const Body:FC<Props> = ({children})=> {
    return (
        <TableBody>
            {children}
        </TableBody>
    );
};

export {Body}