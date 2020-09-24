import styled from 'styled-components';

const Table = styled.table`
  width: 100%;
  color: ${({theme}) => theme.color.grey140};
  border-collapse: collapse;
`;

const TablePlaceholder = styled.div`
  display: grid;
  grid-row-gap: 10px;

  > div {
    height: 54px;
  }
`;

export {Table, TablePlaceholder};
