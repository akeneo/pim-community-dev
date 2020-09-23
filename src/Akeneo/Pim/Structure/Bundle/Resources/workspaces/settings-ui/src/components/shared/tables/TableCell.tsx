import styled from 'styled-components';

const TableCell = styled.td`
  color: ${({theme}) => theme.color.purple100};
  font-style: italic;
  font-weight: bold;
  padding-left: 20px;
`;

export {TableCell};
