import styled, {css} from 'styled-components';

type Props = {
    isFilterable?: boolean;
};

const filterableMixin = css`
  position: sticky;
  top: 44px;
`;

const notFilterableMixin = css`
  position: sticky;
  top: 0;
`;

const TableHeadCell = styled.th<Props>`
  text-align: left;
  font-weight: normal;
  height: calc(44px + 15px);
  box-shadow: 0 1px 0 ${({theme}) => theme.color.grey120};
  background: ${({theme}) => theme.color.white};
  padding-top: 15px;
  
  ${(props) => props.isFilterable ? filterableMixin : notFilterableMixin}

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
