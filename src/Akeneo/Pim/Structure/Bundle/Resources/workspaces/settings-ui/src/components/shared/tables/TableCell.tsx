import React, {FC} from 'react';

type Props = {};

const TableCell: FC<Props> = ({children}) => {
    return (
        <td>{children}</td>
    );
}

export {TableCell};