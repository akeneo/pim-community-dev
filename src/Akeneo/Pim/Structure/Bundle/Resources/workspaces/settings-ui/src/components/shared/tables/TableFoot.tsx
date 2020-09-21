import React, {FC} from 'react';

type Props = {};

const TableFoot: FC<Props> = ({children}) => {
    return (
        <tfoot>{children}</tfoot>
    );
}

export {TableFoot};