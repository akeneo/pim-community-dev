import React, {FC} from 'react';

type Props = {};

const TableBody: FC<Props> = ({children}) => {
    return (
        <tbody>{children}</tbody>
    );
}

export {TableBody};