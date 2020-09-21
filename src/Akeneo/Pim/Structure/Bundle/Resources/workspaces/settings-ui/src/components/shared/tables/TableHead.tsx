import React, {FC} from 'react';

type Props = {};

const TableHead: FC<Props> = ({children}) => {
    return (
        <thead>{children}</thead>
    );
}

export {TableHead};