import React, {FC} from 'react';

type Props = {};

const TableFoot: FC<Props> = ({children, ...props}) => {
    return (
        <tfoot {...props}>{children}</tfoot>
    );
}

export {TableFoot};