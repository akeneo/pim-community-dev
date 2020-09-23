import styled from 'styled-components';

const TableHeadCell = styled.th`
  text-align: left;
  font-weight: normal;
  position: sticky;
  top: 44px;
  height: calc(44px + 15px);
  box-shadow: 0 1px 0 ${({theme}) => theme.color.grey120};
  background: ${({theme}) => theme.color.white};
  padding-top: 15px;

  :first-child {
    padding-left: 20px;
  }
`;

const TableSortableHeadCell = styled(TableHeadCell)`
  &:hover {
    cursor: pointer;
  }
`;

export {TableHeadCell, TableSortableHeadCell};
