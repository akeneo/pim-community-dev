import styled from 'styled-components';

const TableHeadRow = styled.tr`
  cursor: auto;
  height: 59px;
  color: ${({theme}) => theme.color.grey140};
  font-size: 13px;
  font-weight: normal;

  box-shadow: 0 1px 0 ${({theme}) => theme.color.grey120};
`;

export {TableHeadRow};
