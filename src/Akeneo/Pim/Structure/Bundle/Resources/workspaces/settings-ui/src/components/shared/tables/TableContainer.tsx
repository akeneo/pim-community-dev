import React, {FC} from 'react';

type Props = {};

const TableContainer: FC<Props> = ({children}) => {
    return (
        <div className="AknGridContainer">{children}</div>
    );
}

export {TableContainer};