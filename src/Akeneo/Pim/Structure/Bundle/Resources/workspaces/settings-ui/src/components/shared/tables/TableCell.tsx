import React, {FC} from 'react';
import styled from 'styled-components';

type Props = {
  onClick?: () => void;
};

const Cell = styled.td<Props>`
  color: ${({theme}) => theme.color.purple100};
  font-style: italic;
  font-weight: bold;
  padding-left: 20px;
`;

const TableCell: FC<Props> = ({children, ...props}) => {
  return <Cell {...props}>{children}</Cell>;
};

export {TableCell};
