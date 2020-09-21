import React, {FC} from 'react';
import styled from 'styled-components';

type Props = {};

const AknTable = styled.table`
    with: 100%;
`;

const Table: FC<Props> = ({children}) => {
    return (
        <AknTable>{children}</AknTable>
    );
}

export {Table};
