import React, {FC} from 'react';
import styled from 'styled-components';

type Props = {};

const AknTable = styled.table`
  width: 100%;
  color: ${({theme}) => theme.color.grey140};
  border-collapse: collapse;

  td {
    width: 25%;
  }
`;


const TablePlaceholder = styled.div`
  display: grid;
  grid-row-gap: 10px;

  > div {
    height: 54px;
  }
`;

const Table: FC<Props> = ({children}) => {
    return (
        <AknTable>{children}</AknTable>
    );
}

export {Table, TablePlaceholder};
