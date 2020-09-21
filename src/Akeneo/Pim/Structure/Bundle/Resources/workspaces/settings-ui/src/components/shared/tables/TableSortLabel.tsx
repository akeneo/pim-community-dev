import React, {FC} from 'react';

type Props = {};

const TableSortLabel: FC<Props> = ({children}) => {
    return (
        <th>{children}</th>
    );
}

export {TableSortLabel};