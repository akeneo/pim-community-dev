import styled from 'styled-components';

const CellLabel = styled.span`
  width: 71px;
  height: 16px;
  color: ${({theme}) => theme.color.purple100};
  font-size: ${({theme}) => theme.fontSize.default};
  font-weight: bold;
  font-style: italic;
`;

export {CellLabel};
