import styled from "styled-components";

const ColumnLabel = styled.span`
  width: 71px;
  height: 16px;
  color: ${({theme}) => theme.color.purple100};
  font-size: ${({theme}) => theme.fontSize.default};
  font-family: ${({theme}) => theme.font.default};
  font-weight: bold;
  font-style: italic;
`;

export {ColumnLabel};
