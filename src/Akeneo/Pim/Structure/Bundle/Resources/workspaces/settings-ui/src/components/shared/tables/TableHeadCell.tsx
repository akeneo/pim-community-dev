import React, {FC} from 'react';
import styled from 'styled-components';

type Props = {
  onClick?: () => void;
};

type SortProps = Props & {};

const HeadCell = styled.th<Props>`
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

const SortableHeadCell = styled(HeadCell)<SortProps>`
  &:hover {
    cursor: pointer;
  }
`;

const TableHeadCell: FC<Props> = ({children, ...props}) => {
  return <HeadCell {...props}>{children}</HeadCell>;
};

const TableSortableHeadCell: FC<SortProps> = ({children, ...props}) => {
  return <SortableHeadCell {...props}>{children}</SortableHeadCell>;
};

export {TableHeadCell, TableSortableHeadCell};
