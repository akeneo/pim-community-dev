import React, {FC} from 'react';

type Props = {};

const TableHeadCell: FC<Props> = ({children}) => {
    return (
        <th>{children}</th>
    );
}

export {TableHeadCell};