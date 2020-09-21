import React, {FC} from 'react';

type Props = {};

const TableRow: FC<Props> = ({children}) => {
    return (
        <tr>{children}</tr>
    );
}

export {TableRow};